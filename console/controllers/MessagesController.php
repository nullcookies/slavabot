<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 17.01.18
 * Time: 18:06
 */

namespace console\controllers;

use frontend\controllers\bot\libs\jobs\SocialJobs;
use frontend\controllers\bot\libs\SalesBotApi;
use Yii;
use yii\console\Controller;

class MessagesController extends Controller
{
    /**
     * @var SalesBotApi
     */
    protected $salesBot;

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
        set_time_limit(300);
    }

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

    public function actionVk()
    {
        $this->salesBot = new SalesBotApi();

        //for($i = 0; $i < 200; $i++) {
            $this->getUsers();
        //}

        $this->guzzleClient = new \GuzzleHttp\Client();

        //$command = sprintf('php -f yii %s > /dev/null 2>&1 &', 'messages/worker');
        //echo shell_exec($command);

        $this->gearmanClient = new \Kicken\Gearman\Client('127.0.0.1:4730');

        $this->getServers();

        if(empty($this->servers)) {
            echo 'Список Vk long polling серверов пуст' . PHP_EOL;
            Yii::$app->end();
        }

        $this->pool();
    }

    protected function getUsers()
    {
        $users = $this->salesBot->getVkAccounts();

        if($users) {
            foreach ($users as $user) {
                if(!empty($user['telegram_id'] && !empty($user['group_access_token']))) {
                    $this->users[] = $user;
                }
            }
        } else {
            echo 'Нет пользователей' . PHP_EOL;
            Yii::$app->end();
        }

        if(empty($this->users)) {
            echo 'Нет пользователей c подключенными аккаунтами' . PHP_EOL;
            Yii::$app->end();
        }

        var_dump($this->users);
    }

    protected function getServers()
    {
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
        $access_token = $this->servers[$index]['group_access_token'];

        $options = [
            'access_token' => $access_token,
        ];

        $vk = new \frontend\controllers\bot\libs\Vk($options);

        $this->servers[$index] = $vk->api('messages.getLongPollServer', [
            'access_token' => $access_token
        ]);
    }

    protected function pool()
    {
        $count = count($this->servers);

        $loop = 0;
        while ($loop < 20) {

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
                //проверим, что это сообщение и оно входящее
                if(isset($update[0]) && $update[0] == 4 && isset($update[2])) {
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
                }
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

    protected function sendToTelegram($update, $index)
    {
        var_dump($update);

        $workload['update'] = $update;
        $workload['user_id'] = $this->servers[$index]['user_id'];
        $workload['telegram_id'] = $this->servers[$index]['telegram_id'];

        //$client = new \Kicken\Gearman\Client('127.0.0.1:4730');
        //$job = $client->submitBackgroundJob(SocialJobs::FUNCTION_DIALOGUES, \GuzzleHttp\json_encode($update));
        $job = $this->gearmanClient
            ->submitBackgroundJob(SocialJobs::FUNCTION_DIALOGUES, json_encode($workload));
        //echo $job;
    }
}