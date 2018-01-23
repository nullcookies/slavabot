<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 19.01.18
 * Time: 16:02
 */

namespace frontend\controllers\bot\libs\jobs;


use common\models\SocialDialogues;
use common\models\SocialDialoguesPeer;
use frontend\controllers\bot\Bot;
use frontend\controllers\bot\commands\NotificationCommand;
use yii\helpers\ArrayHelper;

class DialoguesJobs implements SocialJobs
{
    public function run(\Kicken\Gearman\Job\WorkerJob $job)
    {
        $workloadJson = $job->getWorkload();
        $workload = json_decode($workloadJson);

        var_dump($workload);

        $user_id = ArrayHelper::getValue($workload, 'user_id');
        $update = ArrayHelper::getValue($workload, 'update');
        $telegram_id = ArrayHelper::getValue($workload, 'telegram_id');
        $group_access_token = ArrayHelper::getValue($workload, 'group_access_token');

        $text = ArrayHelper::getValue($update, 5);
        $peer_id = ArrayHelper::getValue($update, 3);

        $message = SocialDialogues::saveMessage(
            $user_id,
            SocialDialogues::SOCIAL_VK,
            SocialDialogues::TYPE_MESSAGE,
            $update
        );

        $peer = SocialDialoguesPeer::savePeer(SocialDialogues::SOCIAL_VK, $peer_id, $group_access_token);



        echo 'saved' . PHP_EOL;

        $bot = new Bot();
        $telegram = $bot->GetTelegram();
        $command = new NotificationCommand($telegram);
        $command->prepareParams([
            'tid' => $telegram_id,
            'message' => $peer->title.': '.$text
        ]);
        $command->execute();

        echo 'sended' . PHP_EOL;

        $job->sendComplete();
    }
}