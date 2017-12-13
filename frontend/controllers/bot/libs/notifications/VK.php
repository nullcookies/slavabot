<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 04.12.2017
 */

namespace Libs\notifications;

use Models\Posts;

class VK extends NotificationsBase
{
    protected $usersInfo = [];

    public function Run()
    {
        $this->service();
        $this->GetUsers();
    }

    protected function GetUsers()
    {


        $users = $this->salesBot->getVkAccounts();

        if(is_array($users)) {
            foreach ($users as $user) {
//                if($user['telegram_id'] != 45757136)
//                    continue;

                $this->GetNotifications([
                    'tid' => $user['telegram_id'],
                    'access_token' => $user['access_token']
                ]);
            }
        }

    }

    protected function GetNotifications($_params = [])
    {
        $telegram_id = $_params['tid'];
        $access_token = $_params['access_token'];

        $options = [
            'access_token' => $access_token,
        ];

        $model = new NotificationsModel();

        try {

            $startTime = time() - self::LIFETIME;

            $vk = new \Libs\Vk($options);
            $res = $vk->getNotifications([
                'filters' => 'mentions,comments',
                'start_time' => $startTime
            ]);
//            var_dump($res);

            if (isset($res['count']) && $res['count'] > 0) {

                // собираем id юзеров в массив
                foreach ($res['items'] as $k => $item) {
                    $this->usersInfo[$item['feedback']['from_id']] = true;
                }

                // получаем информацию из ВК
                $users = $vk->getUsers([
                    'user_ids' => implode(',', array_keys($this->usersInfo))
                ]);

                foreach ($users as $user) {
                    $this->usersInfo[$user['id']] = $user;
                }

                foreach ($res['items'] as $item) {

//                    var_dump($item);

                    $type = $item['type'];
                    $feedback = $item['feedback'];
                    $text = $feedback['text'];
                    $from_id = $feedback['from_id'];

                    $response_hash = md5($feedback['id'] . $feedback['from_id'] . $feedback['date'] . $feedback['text']);

                    if ($model->existNotification($response_hash)) {
                        continue;
                    }

                    $fullName = $this->usersInfo[$from_id]['first_name'] . ' ' . $this->usersInfo[$from_id]['last_name'];

                    $variants = [
                        'comment_post' => sprintf('Комментарий %s: ', $fullName),
                        'mention' => sprintf('%s упомянул вас: ', $fullName),
                        'mention_comments' => sprintf('Упоминание на стене %s: ', $fullName),
                        'reply_comment' => sprintf('Ответ на комментарий %s: ', $fullName),
                        'comment_video' => sprintf('Комментарий к видеозаписи %s: ', $fullName),
                        'comment_photo' => sprintf('Комментарий к фотографии %s: ', $fullName),
                        'reply_comment_photo' => sprintf('Ответ на комментарий %s: ', $fullName),
                        'reply_comment_video' => sprintf('Ответ на комментарий к видеозаписи %s: ', $fullName),
                        'reply_comment_market' => sprintf('Ответ на комментарий к товару %s: ', $fullName),
                        'reply_topic' => sprintf('Ответ в обсуждении %s: ', $fullName),
                        'mention_comment_photo' => sprintf('Упоминание пользователя в комментарии под фото %s: ',
                            $fullName),
                        'mention_comment_video' => sprintf('Упоминание пользователя в комментарии под видео %s: ',
                            $fullName),
                        'wall' => sprintf('Добавлен комментарий %s', $fullName),
                    ];

                    $text = $variants[$type] . $this->clearText($text);

                    try {

                        $notification = new \Models\Notifications();
                        $notification->SetInternalId($_params['tid']);
                        $notification->SetSocial(Posts::SOCIAL_VK);
                        $notification->SetMessage($text);
                        $notification->SetHash($response_hash);

                        $this->manager->persist($notification);
                        $this->manager->flush();

                        $this->notify([
                            'tid' => $telegram_id,
                            'message' => $text
                        ]);

                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }

                }

            }

        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        usleep(500);
    }

}