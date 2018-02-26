<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 19.02.18
 * Time: 11:19
 */

namespace console\controllers;


use common\models\SocialDialoguesPeerVk;
use common\models\SocialDialoguesVkComments;
use frontend\controllers\bot\Bot;
use frontend\controllers\bot\commands\FrontendNotificationCommand;
use Yii;
use yii\console\Controller;

class VkNotifyController extends Controller
{
    public function actionAdd()
    {
        $users = \common\models\rest\Accounts::find()
            ->andWhere([
                'type' => 'vkontakte',
                'status' => 1,
                'processed' => 1,
                'user_id' => 30
            ])
            ->all();

        if($users) {
            foreach ($users as $user) {
                $user = $user->toArray();
                if(!empty($user['telegram_id'] && !empty($user['access_token']))) {
                    //TODO проверить, что это пользователь. Для групп не запускать
                    $access_token = $user['access_token'];
                    $options = [
                        'access_token' => $access_token,
                    ];
                    try {
                        $vk = new \frontend\controllers\bot\libs\Vk($options);
                        echo $user['user_id'] . PHP_EOL;

                        $result = $vk->api('groups.addCallbackServer');


                    } catch(\Exception $e) {
                        echo $e->getMessage() . PHP_EOL;
                    }
                }
            }
        } else {
            echo 'Нет пользователей' . PHP_EOL;
            Yii::$app->end();
        }
    }

    public function actionNotify()
    {
        $count = 0;
        while (true) {



            $users = \common\models\rest\Accounts::getVk();

            /*$users = \common\models\rest\Accounts::find()
                ->andWhere([
                    'type' => 'vkontakte',
                    'status' => 1,
                    'processed' => 1,
                    'user_id' => 30
                ])
                ->all();*/

            if ($users) {
                foreach ($users as $user) {
                    $user = $user->toArray();
                    if (!empty($user['telegram_id'] && !empty($user['access_token']))) {
                        $access_token = $user['access_token'];
                        $options = [
                            'access_token' => $access_token,
                        ];
                        try {
                            $vk = new \frontend\controllers\bot\libs\Vk($options);
                            echo $user['user_id'] . PHP_EOL;
                            $this->getNotify($vk, $user['user_id'], $user['telegram_id']);
                        } catch (\Exception $e) {
                            echo $e->getMessage() . PHP_EOL;
                        }
                        sleep(1);
                    }
                }
            } else {
                echo 'Нет пользователей' . PHP_EOL;
                Yii::$app->end();
            }



            echo 'COUNT: ' . ++$count . PHP_EOL;
        }
    }

    protected function getNotify(\frontend\controllers\bot\libs\Vk $vk, $userId, $telegramId)
    {
        $notifyes = $vk->api('notifications.get', [
            'count' => 100,
            'filters' => 'mentions'
        ]);

        //var_dump($notifyes['items']);

        if($notifyes['items']) {
            foreach ($notifyes['items'] as $notify) {
                $hashWithDocs = null;
                $hash = null;
                if($notify['type'] == 'mention_comments') {
                    $post = $notify['parent'];
                    $postId = $post['id'];
                    $ownerId = $post['from_id'];
                    $commentsHashes = SocialDialoguesVkComments::getCommentsHashByPostId($postId, $ownerId);
                    $comment = $notify['feedback'];

                    $hash = md5(json_encode($comment['text']));

                    //$hash = md5(json_encode($comment));
                    //echo $hash . PHP_EOL;
                    //var_dump($comment);
                    //если такой комментарий уже есть, то переходим к следующему посту
                    if(in_array($hash, $commentsHashes)) {
                        echo 'Дубль' . PHP_EOL;
                        continue;
                    } else {
                        echo $hash . PHP_EOL;
                        //var_dump($comment['attachments']);
                        $peerId = $comment['from_id'];

                        $model = SocialDialoguesVkComments::newVkComment(
                            $userId,
                            $ownerId,
                            $postId,
                            $comment['id'],
                            $comment['text'],
                            $comment['attachments']? json_encode($comment['attachments']): null,
                            $peerId,
                            $hash
                        );

                        $peerInfo = SocialDialoguesPeerVk::parsePeerInfo(
                            $peerId, $notifyes['groups'], $notifyes['profiles']
                        );

                        SocialDialoguesPeerVk::saveVkPeer(
                            $peerId, $peerInfo['title'], $peerInfo['avatar'], $peerInfo['type']
                        );

                        $bot = new Bot();
                        $telegram = $bot->GetTelegram();

                        $command = new FrontendNotificationCommand($telegram);
                        $command->prepareParams([
                            'tid' => $telegramId,
                            'message' => $peerInfo['title'].":\n".$model->getMessageForTelegram(),
                        ]);

                        $command->execute($ownerId, $postId);

                        echo 'sended' . PHP_EOL;
                    }

                }
            }
        }
    }
}