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
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

class VkController extends Controller
{
    public function actionComments()
    {
        //$users = \common\models\rest\Accounts::getVk();
        $count = 0;
        while (true) {


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
                            if($user['access_token'] != $user['group_access_token']) {
                                $ownerId = -$user['group_id'];
                            } else {
                                $ownerId = $user['group_id'];
                            }
                            $this->getComments($vk, $user['user_id'], $ownerId);
                        } catch(\Exception $e) {
                            echo $e->getMessage() . PHP_EOL;
                        }
                    }
                }
            } else {
                echo 'Нет пользователей' . PHP_EOL;
                Yii::$app->end();
            }

            echo 'COUNT: ' . ++$count . PHP_EOL;
        }

    }

    protected function getComments(\frontend\controllers\bot\libs\Vk $vk, $userId, $ownerId)
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

                sleep(1);
            } while (true);

        }

        sleep(1);
    }
}