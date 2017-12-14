<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 14.12.2017
 */

namespace frontend\controllers\bot\libs\jobs;

interface SocialJobs
{

    public function run(\Kicken\Gearman\Job\WorkerJob $job);

}