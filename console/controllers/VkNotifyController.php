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
                    $access_token = $user['access_token'];
                    $options = [
                        'access_token' => $access_token,
                    ];
                    try {
                        $vk = new \frontend\controllers\bot\libs\Vk($options);
                        echo $user['user_id'] . PHP_EOL;
                        $this->getNotify($vk, $user['user_id']);
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

    protected function getNotify(\frontend\controllers\bot\libs\Vk $vk, $userId)
    {
        $notifyes = $vk->api('notifications.get', [
            'count' => 100,
            'filters' => 'mentions'
        ]);

        //var_dump($notifys['items']);

        if($notifyes['items']) {
            foreach ($notifyes['items'] as $notify) {
                if($notify['type'] == 'mention_comments') {
                    $post = $notify['parent'];
                    $postId = $post['id'];
                    $ownerId = $post['from_id'];
                    $commentsHashes = SocialDialoguesVkComments::getCommentsHashByPostId($postId, $ownerId);
                    $comment = $notify['feedback'];
                    $hash = md5(json_encode($comment));
                    //echo $hash . PHP_EOL;
                    //var_dump($comment);
                    //если такой комментарий уже есть, то переходим к следующему посту
                    if(in_array($hash, $commentsHashes)) {
                        echo 'Дубль' . PHP_EOL;
                        continue;
                    } else {
                        echo $hash . PHP_EOL;
                        $peerId = $comment['from_id'];

                        SocialDialoguesVkComments::newVkComment(
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
                    }

                }
            }
        }
    }
}