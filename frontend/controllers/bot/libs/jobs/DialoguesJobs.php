<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 19.01.18
 * Time: 16:02
 */

namespace frontend\controllers\bot\libs\jobs;


use common\models\rest\Accounts;
use common\models\SocialDialogues;
use common\models\SocialDialoguesPeer;
use frontend\controllers\bot\Bot;
use frontend\controllers\bot\commands\NotificationCommand;
use yii\helpers\ArrayHelper;

class DialoguesJobs implements SocialJobs
{
    public function run(\Kicken\Gearman\Job\WorkerJob $job)
    {
        try {
            $workloadJson = $job->getWorkload();
            $workload = json_decode($workloadJson);

            var_dump($workload);

            $account_id = ArrayHelper::getValue($workload, 'id');
            $group_id = ArrayHelper::getValue($workload, 'group_id');
            $user_id = ArrayHelper::getValue($workload, 'user_id');
            $update = ArrayHelper::getValue($workload, 'update');
            $telegram_id = ArrayHelper::getValue($workload, 'telegram_id');
            $group_access_token = ArrayHelper::getValue($workload, 'group_access_token');

            $text = ArrayHelper::getValue($update, 5);
            $peer_id = ArrayHelper::getValue($update, 3);

            /**
             * @var Accounts $account
             */
            $correctedAccount = false;
            if($account = Accounts::getVkById($account_id)) {
                if($correctedAccount = $account->checkAccount($telegram_id, $group_id, $group_access_token)) {
                    var_dump($correctedAccount);
                    $telegram_id = $correctedAccount['telegram_id'];
                    $group_access_token = $correctedAccount['group_access_token'];
                    $access_token = $correctedAccount['access_token'];
                }
            }

            if($correctedAccount && !$model = SocialDialogues::findDoubleMessage($user_id, SocialDialogues::SOCIAL_VK, SocialDialogues::TYPE_MESSAGE, $update)) {
                $message = SocialDialogues::saveMessage(
                    $user_id,
                    SocialDialogues::SOCIAL_VK,
                    SocialDialogues::TYPE_MESSAGE,
                    $update,
                    $group_access_token,
                    $access_token
                );

                $peerType = SocialDialoguesPeer::getVkPeerType($peer_id);
                $peer = SocialDialoguesPeer::savePeer(
                    SocialDialogues::SOCIAL_VK,
                    $peerType,
                    $peer_id,
                    $group_access_token,
                    $access_token
                );
                $title = $peer->title;
                if($peerType == SocialDialoguesPeer::TYPE_CHAT) {
                    $from_peer_id = ArrayHelper::getValue(ArrayHelper::getValue($update, 6), 'from');
                    $fromPeerType = SocialDialoguesPeer::getVkPeerType($from_peer_id);
                    $peerUser = SocialDialoguesPeer::savePeer(
                        SocialDialogues::SOCIAL_VK,
                        $fromPeerType,
                        $from_peer_id,
                        $group_access_token,
                        $access_token
                    );
                    $title .= '->'.$peerUser->title;
                }

                echo 'saved' . PHP_EOL;



                $bot = new Bot();
                $telegram = $bot->GetTelegram();
                $command = new NotificationCommand($telegram);
                $command->prepareParams([
                    'tid' => $telegram_id,
                    'message' => $title.': '.$message->getMessageForSend()
                ]);
                $command->execute();

                echo 'sended' . PHP_EOL;

            } else {
                if($correctedAccount) {
                    echo 'double' . PHP_EOL;
                } else {
                    echo 'incorrect account' . PHP_EOL;
                }


            }

            $job->sendComplete();


        } catch (\Exception $e) {
            $job->sendFail();
            echo $e->getMessage();
        }
    }
}