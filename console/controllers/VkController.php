<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 13.02.18
 * Time: 18:03
 */

namespace console\controllers;


use common\models\SocialDialoguesPeerVk;
use common\models\SocialDialoguesPostVk;
use common\models\SocialDialoguesVkComments;
use frontend\controllers\bot\Bot;
use frontend\controllers\bot\commands\FrontendNotificationCommand;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

class VkController extends Controller
{
    public function actionSend()
    {
        $user = \common\models\rest\Accounts::find()
            ->andWhere([
                'type' => 'vkontakte',
                'status' => 1,
                'processed' => 1,
                'user_id' => 30
            ])
            ->one();
        $user = $user->toArray();

        $access_token = $user['access_token'];
        $options = [
            'access_token' => $access_token,
        ];

        try {
            $vk = new \frontend\controllers\bot\libs\Vk($options);
            $vk->api('wall.createComment', [
                'owner_id' => -160368639,
                'post_id' => 43,
                'from_group' => 160368639,
                'message' => 'Sended comment'
            ]);


        } catch(\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }

    }

    public function actionComments()
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
                            if($user['access_token'] != $user['group_access_token']) {
                                $ownerId = -$user['group_id'];
                            } else {
                                $ownerId = $user['group_id'];
                            }
                            $this->getComments($vk, $user['user_id'], $ownerId, $user['telegram_id']);
                        } catch(\Exception $e) {
                            echo $e->getMessage() . PHP_EOL;
                        }

                        sleep(3);
                    }
                }
            } else {
                echo 'Нет пользователей' . PHP_EOL;
                Yii::$app->end();
            }

            echo 'COUNT: ' . ++$count . PHP_EOL;
        }

    }

    protected function getComments(\frontend\controllers\bot\libs\Vk $vk, $userId, $ownerId, $telegramId)
    {
        echo $ownerId . PHP_EOL;
        $postIds = [];
        //$postsHashes = SocialDialoguesPostVk::getPostHashByAccountId($userId, $ownerId);

        $wall = $vk->api('wall.get', [
            'owner_id' => $ownerId,
            'count' => 100,
            'extended' => 1
        ]);

        foreach ($wall['items'] as $post) {
            echo $post['date'] . PHP_EOL;
            //var_dump($post);
            $postIds[] = $post['id'];

            /*$hash = md5(json_encode($post));
            //echo $hash . PHP_EOL;

            //если такой комментарий уже есть, то переходим к следующему посту
            if(in_array($hash, $postsHashes)) {
                echo 'Дубль' . PHP_EOL;
                continue;
            } else {
                echo $hash . PHP_EOL;
                $peerId = $post['from_id'];

                //TODO изменить хэш и добавить цикл по репостам
                SocialDialoguesPostVk::newVkPost(
                    $userId,
                    $ownerId,
                    $post['id'],
                    $peerId,
                    $post['text'],
                    $post['attachments']? json_encode($post['attachments']): null,
                    $hash,
                    $relatedPostId
                );

                $peerInfo = SocialDialoguesPeerVk::parsePeerInfo(
                    $peerId, $wall['groups'], $wall['profiles']
                );

                SocialDialoguesPeerVk::saveVkPeer(
                    $peerId, $peerInfo['title'], $peerInfo['avatar'], $peerInfo['type']
                );
            }*/
        }

        foreach ($postIds as $postId) {
            $commentsHashes = SocialDialoguesVkComments::getCommentsHashByPostId($postId, $ownerId);
            $offset = 0;
            do {
                sleep(3);

                $comments = $vk->api('wall.getComments', [
                    'owner_id' => $ownerId,
                    'post_id' => $postId,
                    'count' => 100,
                    'offset' => $offset,
                    'sort' => 'asc',
                    'extended' => 1
                ]);

                //var_dump($comments);

                if($comments['items']) {
                    foreach ($comments['items'] as $comment) {
                        $hash = md5(json_encode($comment));
                        //echo $hash . PHP_EOL;
                        //var_dump($comment);
                        //если такой комментарий уже есть, то переходим к следующему посту
                        if(in_array($hash, $commentsHashes)) {
                            echo 'Дубль' . PHP_EOL;
                            continue;
                        } elseif($comment['from_id'] != $ownerId) {
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
                                $peerId, $comments['groups'], $comments['profiles']
                            );

                            SocialDialoguesPeerVk::saveVkPeer(
                                $peerId, $peerInfo['title'], $peerInfo['avatar'], $peerInfo['type']
                            );

                            $bot = new Bot();
                            $telegram = $bot->GetTelegram();

                            $command = new FrontendNotificationCommand($telegram);
                            $command->prepareParams([
                                'tid' => $telegramId,
                                'message' => $peerInfo['title'].":\n".$comment['text'],
                            ]);
                            $command->execute($ownerId, $postId);

                            echo 'sended' . PHP_EOL;
                        } else {
                            echo 'Исходящий' . PHP_EOL;
                        }
                    }
                    if($comments['count'] < 100) {
                        break;
                    } else {
                        $offset += 100;
                    }

                } else {
                    break;
                }
            } while (true);

        }
    }
}