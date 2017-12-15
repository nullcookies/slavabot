<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 14.12.2017
 */

namespace frontend\controllers\bot\libs\jobs;

interface SocialJobs
{
    const FUNCTION_VK = 'sales_post_vk';
    const FUNCTION_FB = 'sales_post_fb';
    const FUNCTION_IG = 'sales_post_ig';

    public function run(\Kicken\Gearman\Job\WorkerJob $job);

}