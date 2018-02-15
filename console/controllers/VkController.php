<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 13.02.18
 * Time: 18:03
 */

namespace console\controllers;


use Yii;
use yii\console\Controller;

class VkController extends Controller
{
    public function actionComments()
    {
        //$users = \common\models\rest\Accounts::getVk();

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
                        $this->getComments($vk, $ownerId);
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

    protected function getComments(\frontend\controllers\bot\libs\Vk $vk, $ownerId)
    {
        //проверяется на глубину одной недели и не более 100 постов
        $minTimestamp = time() - 60 * 60 * 24 * 7;
        $postIds = [];
        $wall = $vk->api('wall.get', [
            'owner_id' => $ownerId,
            'count' => 100,
            'extended' => 1
        ]);

        foreach ($wall['items'] as $post) {
            if($post['date'] < $minTimestamp) {
                //break;
            }
            echo $post['date'] . PHP_EOL;
            var_dump($post);
            $postIds[] = $post['id'];
        }


        foreach ($postIds as $postId) {
            $offset = 0;
            do {
                $comments = $vk->api('wall.getComments', [
                    'owner_id' => $ownerId,
                    'post_id' => $postId,
                    'count' => 100,
                    'offset' => $offset,
                    'sort' => 'asc'
                ]);

                if($comments['items']) {
                    foreach ($comments['items'] as $comment) {
                        var_dump($comment);
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