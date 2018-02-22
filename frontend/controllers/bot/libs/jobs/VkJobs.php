<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 14.12.2017
 */

namespace frontend\controllers\bot\libs\jobs;

use common\commands\command\EditTelegramNotificationCommand;
use common\commands\command\CheckStatusNotificationCommand;

use common\models\JobPost;
use common\models\Post;
use frontend\controllers\bot\libs\Files;
use frontend\controllers\bot\libs\Logger;
use frontend\controllers\bot\libs\SalesBotApi;
use frontend\controllers\bot\libs\SocialNetworks;
use Vk;

class VkJobs implements SocialJobs
{
    public function run(\Kicken\Gearman\Job\WorkerJob $job)
    {
        try {
            $notes = json_decode($job->getWorkload(), true);
            echo "Задача публикации в ВК запущена \n";

            Logger::info('Задача публикации в ВК запущена', [
                'notes' => $notes
            ]);

            //todo убрать
            $job->sendComplete();

            if (!is_array($notes)) {
                $notes = json_decode($notes, true);
            }

            $vk = new Vk($notes['access']);
            $owner = $notes['wall_id'];
            $attachments = null;
            $messages = null;
            $result = null;
            $postModelId = $notes['post_model_id'];


            if (isset($notes['Text']) && !empty($notes['Text'])) {
                $messages = $notes['Text'];
            }

            if (isset($notes['Photos']) && is_array($notes['Photos'])) {

                $waitExists = Files::WaitExists($notes['Photos']);
                if ($waitExists == true) {
                    echo "Files uploaded";
                    $attachments = $vk->upload_photo(0, $notes['Photos'], false);
                } else {
                    Logger::error('Ошибка получения фото из telegram', [
                        'notes' => $notes
                    ]);
                    throw new \Exception("Ошибка получения файлов из telegram");
                }
            }


            if (isset($notes['Videos']) && is_array($notes['Videos'])) {


                $waitExists = Files::WaitExists($notes['Videos']);
                if ($waitExists == true) {
                    echo "Video files uploaded";

//                    [
//                        'link' => $video_url,
//                        'wallpost' => 0
//                    ]

                    $attachments = $vk->upload_video(['group_id' => preg_replace("/[^0-9]/", '', $owner)],
                        $notes['Videos'][0]);

//                    $attachments = $vk->upload_video(
//                        array('name' => 'Test video',
//                            'description' => 'My description',
//                            'wallpost' => 1,
//                            'group_id' => 0
//                        ), 'video.mp4');


                    Logger::info('Видео:', [
                        'result' => json_encode($attachments) . ' Инфо о видео: ' . json_encode($notes['Videos'][0])
                    ]);
                } else {
                    Logger::error('Ошибка получения видео из telegram', [
                        'notes' => $notes
                    ]);
                    throw new \Exception("Ошибка получения файлов из telegram");
                }
            }

            if (isset ($notes['Text'])) {
                $result = $vk->api('wall.post', [
                        'owner_id' => $owner,
                        'message' => $messages,
                        'attachments' => $attachments,
                        'from_group' => '1',
                        'guid' => $postModelId
                    ]
                );

                echo "Публикация записи - " . json_encode($result) . " \n";
                Logger::info('Публикация записи', [
                    'result' => json_encode($result)
                ]);
            }

            $SalesBot = new \frontend\controllers\bot\libs\SalesBotApi();

            /** @var \common\models\Post $post */
            $post = \common\models\Post::findOne([
                'id' => $postModelId
            ]);


            if ($post) {

                $post->job_result = json_encode($result);
                $post->job_status = \common\models\Post::JOB_STATUS_POSTED;
                $post->save(false);
                $data =  [
                    'callback_tlg_message_status' => $post->callback_tlg_message_status
                ];

                $elseData = $data;
                $data['job_status'] = 'POSTED';
                $elseData['job_status'] = 'FAIL';

                $count = Post::find()->where(['OR', $data, $elseData])->count();
                //отправляем в api
                $arParam = ['data' => json_encode($post->getAttributes()), 'type' => SocialNetworks::VK, 'tid' => 0];
                var_dump($SalesBot->newEvent($arParam));

                try {
                    \Yii::$app->commandBus->handle(
                        new EditTelegramNotificationCommand(
                            [
                                'data' => $notes['response_data']
                            ]
                        )
                    );
                } catch (\Exception $e) {
                    Logger::error($e->getMessage());
                }

            }

            /** @var JobPost $jobPost */
            $jobPost = \common\models\JobPost::findOne([
                'post_id' => $postModelId,
                'social' => \common\models\Post::SOCIAL_VK
            ]);

            if ($jobPost) {
                $jobPost->status = JobPost::JOB_STATUS_POSTED;
                $jobPost->execute_dt = \Carbon\Carbon::now('Europe/London');
                $jobPost->save(false);

                //отправляем в api
                $arParam = ['data' => json_encode($jobPost->getAttributes()), 'type' => SocialNetworks::VK, 'tid' => 0];
                $SalesBot->newEvent($arParam);
            }else{
                try {
                    \Yii::$app->commandBus->handle(
                        new CheckStatusNotificationCommand(
                            [
                                'data' => [
                                    'callback_tlg_message_status' => $post->callback_tlg_message_status,
                                    'count' => $count
                                ]
                            ]
                        )
                    );
                } catch (\Exception $e) {
                    return ($e->getMessage());
                }
            }


            Logger::info('Публикация ВК завершена');

            return true;

        } catch (\Exception $e) {

            echo $e->getMessage();

            Logger::error('Error jobs VK', [
                'method' => __METHOD__,
                'message' => $e->getMessage()
            ]);

            $SalesBot = new SalesBotApi();

            /** @var Post $post */
            $post = Post::findOne([
                'id' => $postModelId
            ]);

            if ($post) {
                $post->job_status = Post::JOB_STATUS_FAIL;
                $post->job_error = $e->getTraceAsString();
                $post->save(false);
                $data =  [
                    'callback_tlg_message_status' => $post->callback_tlg_message_status
                ];

                $elseData = $data;
                $data['job_status'] = 'POSTED';
                $elseData['job_status'] = 'FAIL';

                $count = Post::find()->where(['OR', $data, $elseData])->count();
                //отправляем в api
                $arParam = ['data' => json_encode($post->getAttributes()), 'type' => SocialNetworks::VK, 'tid' => 0];
                $SalesBot->newEvent($arParam);

                $arParam = [
                    'wall_id' => $post->wall_id,
                    'status' => 0
                ];
                $SalesBot->setUserAccountStatus($arParam);

                try {
                    $notes['response_data']['text'] = 'Вконтакте - ошибка';
                    \Yii::$app->commandBus->handle(
                        new EditTelegramNotificationCommand(
                            [
                                'data' => $notes['response_data']
                            ]
                        )
                    );
                } catch (\Exception $e) {
                    Logger::error($e->getMessage());
                }

            }

            $jobPost = JobPost::findOne([
                'post_id' => $postModelId,
                'social' => Post::SOCIAL_VK
            ]);

            if ($jobPost) {
                $jobPost->status = JobPost::JOB_STATUS_FAIL;
                $jobPost->execute_dt = \Carbon\Carbon::now('Europe/London');
                $jobPost->save(false);

                //отправляем в api
                $arParam = ['data' => json_encode($jobPost->getAttributes()), 'type' => SocialNetworks::VK, 'tid' => 0];
                $SalesBot->newEvent($arParam);
            } else {
                try {
                    \Yii::$app->commandBus->handle(
                        new CheckStatusNotificationCommand(
                            [
                                'data' => [
                                    'callback_tlg_message_status' => $post->callback_tlg_message_status
                                ],
                                'count' => $count
                            ]
                        )
                    );
                } catch (\Exception $e) {
                    Logger::error($e->getMessage());
                }
            }

            $job->sendComplete();
            return "Error jobs VK";
        }
    }
}