<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 14.12.2017
 */

namespace console\controllers;

use yii\console\Controller;

class PostingController extends Controller
{
    public function actionVk()
    {
        set_time_limit(600);

        $worker = new \Kicken\Gearman\Worker('127.0.0.1:4730');
        $worker
            ->registerFunction('sales_post_vk', function (\Kicken\Gearman\Job\WorkerJob $job) {
                $vkJobs = new \frontend\controllers\bot\libs\jobs\VkJobs();
                $vkJobs->run($job);
            })
            ->work();
        echo 2;
    }

    public function fb()
    {

    }

    public function ig()
    {

    }

}