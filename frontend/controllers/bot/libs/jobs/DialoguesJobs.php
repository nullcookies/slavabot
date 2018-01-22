<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 19.01.18
 * Time: 16:02
 */

namespace frontend\controllers\bot\libs\jobs;


use common\models\SocialDialogues;
use frontend\controllers\bot\Bot;
use frontend\controllers\bot\commands\NotificationCommand;

class DialoguesJobs implements SocialJobs
{
    public function run(\Kicken\Gearman\Job\WorkerJob $job)
    {
        $workload = $job->getWorkload();

        //echo $job->getWorkload();

        $model = SocialDialogues::saveMessage(
            1111,
            SocialDialogues::SOCIAL_VK,
            SocialDialogues::TYPE_MESSAGE,
            '7777'
        );

        $bot = new Bot();
        $telegram = $bot->GetTelegram();
        $command = new NotificationCommand($telegram);
        $command->prepareParams([
            //'tid' => $workload['telegram_id'],
            'tid' => 226171611,
            //'message' => $model->peer_title.': '.$workload['update'][5]
            'message' => $workload
        ]);
        $command->execute();

        $job->sendComplete();
    }
}