<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 14.12.2017
 */

namespace console\controllers;

use common\models\JobPost;
use common\models\Post;
use frontend\controllers\bot\libs\jobs\SocialJobs;
use frontend\controllers\bot\libs\Logger;
use frontend\controllers\bot\libs\notifications\VK;
use yii\console\Controller;

class PostingController extends Controller
{
    protected $routes = [
        'posting/vk',
        'posting/fb',
        'posting/ig'
    ];

    protected $commandPS = "ps ax | grep '%s' | grep -v grep | awk '{print $1}'";
    protected $commandKill = "kill %s";

    public function actionStart()
    {
        foreach ($this->routes as $route) {
            $command = sprintf('php -f yii %s > /dev/null 2>&1 &', $route);
            echo shell_exec($command);
        }
    }

    public function actionStop()
    {
        $routePids = $this->getPidArray();

        foreach ($routePids as $pids) {
            foreach ($pids as $pid) {
                $command = sprintf($this->commandKill, $pid);
                echo shell_exec($command);
            }
        }
    }

    protected function getPidArray()
    {
        $pids = [];

        foreach ($this->routes as $route) {
            $command = sprintf($this->commandPS, $route);
            $output = shell_exec($command);

            if (preg_match_all('/([\d]+)/', $output, $match)) {
                $pids[$route] = $match[0];
            }
        }

        return $pids;
    }

    public function actionPids()
    {
        foreach ($this->routes as $route) {
            $command = sprintf($this->commandPS, $route);
            echo shell_exec($command);
        }
    }

    public function actionShedule()
    {
        //Logger::info('Запуск Posting/shedule', [], 'shedule');
        $this->postponedPost();
        $this->notification();
    }

    protected function notification()
    {
        $vk = new VK();
//        $vk->Run();
    }

    protected function postponedPost()
    {
        //Logger::info('Отложенная публикация');

        $posts = JobPost::find()->where(['status' => JobPost::JOB_STATUS_QUEUED])->andWhere([
            '<=',
            'schedule_dt',
            \Carbon\Carbon::now('Europe/London')->toDateTimeString()
        ])->all();

        /** @var JobPost $post */
        foreach ($posts as $post)
        {
            $social = $post->social;
            $payload = $post->payload;

            switch ($social) {
                case Post::SOCIAL_VK:
                    $client = new \Kicken\Gearman\Client('127.0.0.1:4730');
                    $job = $client->submitBackgroundJob(SocialJobs::FUNCTION_VK, json_encode($payload));
                    break;

                case Post::SOCIAL_FB:
                    $client = new \Kicken\Gearman\Client('127.0.0.1:4730');
                    $job = $client->submitBackgroundJob(SocialJobs::FUNCTION_FB, json_encode($payload));
                    break;

                case Post::SOCIAL_IG:
                    $client = new \Kicken\Gearman\Client('127.0.0.1:4730');
                    $job = $client->submitBackgroundJob(SocialJobs::FUNCTION_IG, json_encode($payload));
                    break;

                default:
            }

        }
    }

    public function actionVk()
    {
        set_time_limit(600);

        $worker = new \Kicken\Gearman\Worker('127.0.0.1:4730');
        $worker
            ->registerFunction(SocialJobs::FUNCTION_VK, function (\Kicken\Gearman\Job\WorkerJob $job) {
                $vkJobs = new \frontend\controllers\bot\libs\jobs\VkJobs();
                $vkJobs->run($job);
            })
            ->work();
    }

    public function actionFb()
    {
        set_time_limit(600);

        $worker = new \Kicken\Gearman\Worker('127.0.0.1:4730');
        $worker
            ->registerFunction(SocialJobs::FUNCTION_FB, function (\Kicken\Gearman\Job\WorkerJob $job) {
                $vkJobs = new \frontend\controllers\bot\libs\jobs\FbJobs();
                $vkJobs->run($job);
            })
            ->work();
    }

    public function actionIg()
    {
        set_time_limit(600);

        $worker = new \Kicken\Gearman\Worker('127.0.0.1:4730');
        $worker
            ->registerFunction(SocialJobs::FUNCTION_IG, function (\Kicken\Gearman\Job\WorkerJob $job) {
                $vkJobs = new \frontend\controllers\bot\libs\jobs\IGJobs();
                $vkJobs->run($job);
            })
            ->work();
    }

}