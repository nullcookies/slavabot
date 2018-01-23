<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 17.01.18
 * Time: 18:06
 */

namespace console\controllers;

use common\models\Accounts;
use frontend\controllers\bot\libs\jobs\SocialJobs;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

class MessagesController extends Controller
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $guzzleClient;

    /**
     * @var \Kicken\Gearman\Client
     */
    protected $gearmanClient;

    protected $users = [];

    public $servers = [];

    public function init()
    {
        set_time_limit(0);
    }

    /**
     * Запуск воркера
     */
    public function actionWorker()
    {
        $worker = new \Kicken\Gearman\Worker('127.0.0.1:4730');
        $worker
            ->registerFunction(SocialJobs::FUNCTION_DIALOGUES, function (\Kicken\Gearman\Job\WorkerJob $job) {
                $dJobs = new \frontend\controllers\bot\libs\jobs\DialoguesJobs();
                $dJobs->run($job);
            })
            ->work();
    }

    /**
     * Запуск основного цикла long-poll запросов
     */
    public function actionVk()
    {
        $this->guzzleClient = new \GuzzleHttp\Client();

        $this->gearmanClient = new \Kicken\Gearman\Client('127.0.0.1:4730');

        $this->getUsers();

        $this->getServers();

        if(empty($this->servers)) {
            echo 'Список Vk long polling серверов пуст' . PHP_EOL;
            Yii::$app->end();
        }

        echo 'users: ' . PHP_EOL;
        var_dump($this->users);

        echo 'servers: ' . PHP_EOL;
        var_dump($this->servers);

        $this->pool();
    }

    protected function getUsers()
    {
        $users = \common\models\rest\Accounts::getVk();

        if($users) {
            foreach ($users as $user) {
                $user = $user->toArray();
                if(!empty($user['telegram_id'] && !empty($user['group_access_token']))) {
                    if(empty($user['ts']) || empty($user['key']) || empty($user['server'])) {
                        $this->users[] = $user;
                    } else {
                        $this->servers[] = $user;
                    }

                }
            }
        } else {
            echo 'Нет пользователей' . PHP_EOL;
            Yii::$app->end();
        }

        if(empty($this->users) && empty($this->servers)) {
            echo 'Нет пользователей c подключенными аккаунтами' . PHP_EOL;
            Yii::$app->end();
        }
    }

    protected function getServers()
    {
        if($this->users) {
            $count = count($this->users);

            $requests = function ($total) {
                for ($i = 0; $i < $total; $i++) {
                    $access_token = $this->users[$i]['group_access_token'];
                    $uri = "https://api.vk.com/method/messages.getLongPollServer?access_token={$access_token}&v=5.69";
                    yield new \GuzzleHttp\Psr7\Request('GET', $uri);
                }
            };

            $pool = new \GuzzleHttp\Pool($this->guzzleClient, $requests($count), [
                'concurrency' => $count,
                'fulfilled' => function ($response, $index) {
                    // this is delivered each successful response
                    $this->serverResponse($response, $index);
                },
                'rejected' => function ($reason, $index) {
                    // this is delivered each failed request
                },
            ]);

            // Initiate the transfers and create a promise
            $promise = $pool->promise();

            // Force the pool of requests to complete.
            $promise->wait();
        }
    }

    protected function serverResponse(\GuzzleHttp\Psr7\Response $response, $index)
    {
        $server = \GuzzleHttp\json_decode($response->getBody()->getContents());

        if($server->response) {
            $serverResult = $this->users[$index];
            $serverResult['ts'] = $server->response->ts;
            $serverResult['key'] = $server->response->key;
            $serverResult['server'] = $server->response->server;
            $this->servers[] = $serverResult;
        }
    }

    protected function updateServer($index)
    {
        echo 'update#'.$index.PHP_EOL;

        $access_token = $this->servers[$index]['group_access_token'];

        $options = [
            'access_token' => $access_token,
        ];

        $vk = new \frontend\controllers\bot\libs\Vk($options);

        $server = $vk->api('messages.getLongPollServer', [
            'access_token' => $access_token
        ]);

        $this->servers[$index]['ts'] = $server['ts'];
        $this->servers[$index]['key'] = $server['key'];
        $this->servers[$index]['server'] = $server['server'];
    }

    protected function pool()
    {
        $loop = 0;
        while (true) {

            $count = count($this->servers);

            $requests = function ($total) {
                for ($i = 0; $i < $total; $i++) {
                    $server = $this->servers[$i]['server'];
                    $key = $this->servers[$i]['key'];
                    $ts = $this->servers[$i]['ts'];
                    $uri = "https://{$server}?act=a_check&key={$key}&ts={$ts}&wait=25&mode=2&version=2";

                    yield new \GuzzleHttp\Psr7\Request('GET', $uri);
                }
            };

            $pool = new \GuzzleHttp\Pool($this->guzzleClient, $requests($count), [
                'concurrency' => $count,
                'fulfilled' => function ($response, $index) {
                    // this is delivered each successful response
                    $this->poolResponse($response, $index);
                },
                'rejected' => function ($reason, $index) {
                    // this is delivered each failed request
                },
            ]);

            // Initiate the transfers and create a promise
            $promise = $pool->promise();

            // Force the pool of requests to complete.
            $promise->wait();

            $loop++;

            if(($loop % 10) == 0) {
                echo $loop . PHP_EOL;

                $this->renewConnections();

                $this->saveLongPollParams();

                $this->users = [];

                $this->servers = [];

                $this->getUsers();

                $this->getServers();

                echo 'updated users: ' . PHP_EOL;
                var_dump($this->users);

                echo 'updated servers: ' . PHP_EOL;
                var_dump($this->servers);
            }
        }


    }

    protected function renewConnections()
    {
        if (isset(\Yii::$app->db)) {
            \Yii::$app->db->close();
            \Yii::$app->db->open();
        }
    }

    protected function saveLongPollParams()
    {
        if($accounts = Accounts::findAll(ArrayHelper::getColumn($this->servers, 'id'))) {
            $servers = ArrayHelper::index($this->servers, 'id');
            foreach ($accounts as $account) {
                $server = $servers[$account->id];
                $data = json_decode($account->data, true);
                if($data['groups']['id'] == $server['group_id']) {
                    $data['groups']['ts'] = $server['ts'];
                    $data['groups']['key'] = $server['key'];
                    $data['groups']['server'] = $server['server'];
                }
                $data = json_encode($data);
                $account->data = $data;
                if(!$account->save(false)) {
                    var_dump($account->errors);
                }
            }
        }
    }

    protected function poolResponse(\GuzzleHttp\Psr7\Response $response, $index)
    {
        $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

        if($content->ts) {
            $this->servers[$index]['ts'] = $content->ts;
        }

        if($content->updates) {
            foreach ($content->updates as $update) {
                //проверим, что это сообщение
                if(isset($update[0]) && ($update[0] == 4 || $update[0] == 5)) {
                    $this->sendToTelegram($update, $index);
                }
                //проверим, что это сообщение и оно входящее
                /*if(isset($update[0]) && $update[0] == 4 && isset($update[2])) {
                    $flag = $update[2];
                    $summands = [];
                    foreach([1, 2, 4, 8, 16, 32, 64, 128, 256, 512, 65536] as $number) {
                        if ($flag & $number) {
                            $summands[] = $number;
                        }
                    }
                    if(!in_array(2, $summands)) {
                        $this->sendToTelegram($update, $index);
                    }
                }*/
            }
        }

        if($content->failed) {
            switch($content->failed) {
                case 1:
                    //обновили выше
                    break;
                case 2:
                    $this->updateServer($index);
                    break;
                case 3:
                    $this->updateServer($index);
                    break;
                case 4:
                    //Vk long polling: передан недопустимый номер версии в параметре version
                    break;
                default:
                    //Vk long polling: неизвестная ошибка
            }
        }
    }

    protected function sendToTelegram(array $update, $index)
    {
        var_dump($update);

        echo 'index: ' . $index . PHP_EOL;

        var_dump($this->servers[$index]);

        $workload['update'] = $update;
        $workload['id'] = $this->servers[$index]['id'];
        $workload['user_id'] = $this->servers[$index]['user_id'];
        $workload['group_id'] = $this->servers[$index]['group_id'];
        $workload['telegram_id'] = $this->servers[$index]['telegram_id'];
        $workload['group_access_token'] = $this->servers[$index]['group_access_token'];

        $job = $this->gearmanClient
            ->submitBackgroundJob(SocialJobs::FUNCTION_DIALOGUES, \GuzzleHttp\json_encode($workload));
    }
}