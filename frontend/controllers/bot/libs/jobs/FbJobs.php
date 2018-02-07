<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 14.12.2017
 */

namespace frontend\controllers\bot\libs\jobs;

use common\models\JobPost;
use common\models\Post;
use common\services\StaticConfig;
use frontend\controllers\bot\libs\Files;
use frontend\controllers\bot\libs\Logger;
use frontend\controllers\bot\libs\SalesBotApi;
use frontend\controllers\bot\libs\SocialNetworks;
use Facebook\Facebook;
use common\commands\command\EditTelegramNotificationCommand;
use common\commands\command\CheckStatusNotificationCommand;



class FbJobs implements SocialJobs
{

    public function run(\Kicken\Gearman\Job\WorkerJob $job)
    {
        try {
            $notes = json_decode($job->getWorkload(), true);
            echo "Задача публикации в FB запущена \n";
            Logger::info('Задача публикации в FB запущена', [
                'notes' => $notes
            ]);

            if (!is_array($notes)) {
                $notes = json_decode($notes, true);
            }

            $fb = new Facebook($notes['access']);

            //получаем доп. ключ для публикации на стену группы
            if ($notes['is_group']) {
                $user_token = $notes['page_access_token'];

                $responseAccounts = json_decode($fb->get('/me/accounts', "{$user_token}")->getBody(), true);
                $notes['page_access_token'] = $responseAccounts['data'][0]['access_token'];
            }

            $link = null;
            $attachments = null;
            $messages = null;
            $access_token = $notes['page_access_token'];
            $page_id = $notes['page_id'];
            $postModelId = $notes['post_model_id'];

            if (isset($notes['Text']) && !empty($notes['Text'])) {
                $messages = $notes['Text'];
            }
            $object = null;

            $linkData = [
                'message' => $messages,
                'published' => 'true',
            ];

            if (isset($notes['Photos']) && is_array($notes['Photos'])) {

                foreach ($notes['Photos'] as $strPhoto) {

                    $arPhoto = json_decode($strPhoto, true);

                    $file_uri = StaticConfig::getDownloadDir() . $arPhoto[0]["file_path"];
                    Logger::error('file_uri', [
                        'f' => $file_uri
                    ]);

                    $waitExists = Files::WaitExists([\Yii::getAlias('@webroot') . $file_uri]);
                    if ($waitExists == true) {

                        $link = rtrim($notes['hostname'], '/') . $file_uri;

                        Logger::info('Изображение', [
                            'link' => $link
                        ]);

                        $uploadPhoto = $fb->post("/{$page_id}/photos", ['url' => $link, 'published' => 'false'],
                            $access_token);

                        echo "Загрузка картинки FB - {$uploadPhoto->getBody()} \n";

                        $graphNode = $uploadPhoto->getGraphNode();

                        //todo сделать множественную загрузку
                        $linkData['attached_media[]'] = '{"media_fbid":"' . $graphNode['id'] . '"}';

                    } else {
                        Logger::error('Ошибка получения файлов из telegram', [
                            'file' => __FILE__,
                            'notes' => $notes,
                            'link' => $link
                        ]);
                        throw new \Exception("Ошибка получения файлов из telegram");
                    }
                }
            }





            //todo включить видео
            if (isset($notes['Videos']) && is_array($notes['Videos'])) {
                $waitExists = Files::WaitExists($notes['Videos']);
                if ($waitExists == true) {
                    echo "Video files uploaded";
                    $link = rtrim($notes['hostname'], '/') . '/storage/download/videos/' . basename($notes['Videos'][0]);
                    $response = $fb->post("/{$page_id}/videos", ['description' => $messages, 'file_url' => $link,], $access_token);
                    $uploadVideo = $response->getGraphNode()->asArray();


                } else {

                    throw new Exception("Ошибка получения файлов из telegram");
                }
            }else{
                $response = $fb->post(
                    "/{$page_id}/feed",
                    $linkData,
                    $access_token);
            }

            //todo удалить
            $job->sendComplete();

            echo "Публикация записи FB - {$response->getBody()} \n";
            Logger::info('Публикация записи FB', [
                'result' => $response->getBody()
            ]);

            $SalesBot = new SalesBotApi();

            /** @var Post $post */
            $post = Post::findOne([
                'id' => $postModelId
            ]);

            if ($post) {
                $post->job_status = Post::JOB_STATUS_POSTED;
                $post->job_result = json_encode($response->getBody());
                $post->save(false);
                $data =  [
                    'callback_tlg_message_status' => $post->callback_tlg_message_status
                ];

                $elseData = $data;
                $data['job_status'] = 'POSTED';
                $elseData['job_status'] = 'FAIL';

                $count = Post::find()->where(['OR', $data, $elseData])->count();

                //отправляем в api
                $arParam = ['data' => json_encode($post->toArray()), 'type' => SocialNetworks::FB, 'tid' => 0];
                $SalesBot->newEvent($arParam);

                try{
                    \Yii::$app->commandBus->handle(
                        new EditTelegramNotificationCommand(
                            [
                                'data' => $notes['response_data']
                            ]
                        )
                    );
                }catch (\Exception $e){
                    Logger::error($e->getMessage());
                }
            }


            /** @var JobPost $jobPost */
            $jobPost = JobPost::findOne([
                'post_id' => $postModelId,
                'social' => Post::SOCIAL_FB
            ]);

            if ($jobPost) {
                $jobPost->status = JobPost::JOB_STATUS_POSTED;
                $jobPost->execute_dt = \Carbon\Carbon::now('Europe/London');
                $jobPost->save(false);


                //отправляем в api
                $arParam = ['data' => json_encode($jobPost->getAttributes()), 'type' => SocialNetworks::FB, 'tid' => 0];
                $SalesBot->newEvent($arParam);
            }else{
                try{
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
                }catch (\Exception $e){
                    return ($e->getMessage());
                }
            }

            Logger::info('Публикация FB завершена');

            return true;

        } catch (\Exception $e) {

            Logger::error('Error jobs FB', [
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
                $arParam = ['data' => json_encode($post->toArray()), 'type' => SocialNetworks::FB, 'tid' => 0];
                $SalesBot->newEvent($arParam);

                $arParam = [
                    'wall_id' => $post->wall_id,
                    'status' => 0
                ];
                try{
                    $notes['response_data']['text'] = 'Facebook - ошибка';
                    \Yii::$app->commandBus->handle(
                        new EditTelegramNotificationCommand(
                            [
                                'data' => $notes['response_data']
                            ]
                        )
                    );
                }catch (\Exception $e){
                    Logger::error($e->getMessage());
                }

                var_dump($SalesBot->setUserAccountStatus($arParam));
            }


            /** @var JobPost $jobPost */
            $jobPost = JobPost::findOne([
                'post_id' => $postModelId,
                'social' => Post::SOCIAL_FB
            ]);

            if ($jobPost) {
                $jobPost->status = JobPost::JOB_STATUS_FAIL;
                $jobPost->execute_dt = \Carbon\Carbon::now('Europe/London');
                $jobPost->save(false);

                //отправляем в api
                $arParam = ['data' => json_encode($jobPost->getAttributes()), 'type' => SocialNetworks::FB, 'tid' => 0];
                $SalesBot->newEvent($arParam);
            }else{
                try{
                    \Yii::$app->commandBus->handle(
                        new CheckStatusNotificationCommand(
                            [
                                'data' => [
                                    'callback_tlg_message_status' => $post->callback_tlg_message_status,

                                ],
                                'count' => $count
                            ]
                        )
                    );
                }catch (\Exception $e){
                    Logger::error($e->getMessage());
                }
            }

            $job->sendComplete();
            return "Error jobs FB";
        }
    }
}