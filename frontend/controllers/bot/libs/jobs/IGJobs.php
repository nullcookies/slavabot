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
use FFMpeg\Coordinate\Dimension;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use frontend\controllers\bot\libs\Files;
use frontend\controllers\bot\libs\Logger;
use frontend\controllers\bot\libs\SalesBotApi;
use frontend\controllers\bot\libs\SocialNetworks;
use common\commands\command\EditTelegramNotificationCommand;
use common\commands\command\CheckStatusNotificationCommand;



class IGJobs implements SocialJobs
{
    public function run(\Kicken\Gearman\Job\WorkerJob $job)
    {
        try {

            $command = 'sudo chown -R apache:apache /var/www/salesbot/vendor/mgp25/instagram-php/sessions';
            echo shell_exec($command);

            $notes = json_decode($job->getWorkload(), true);

            $job->sendComplete();

            echo "Задача публикации в IG запущена \n";
            Logger::info('Задача публикации в IG запущена', [
                'notes' => $notes
            ]);

            if (!is_array($notes)) {
                $notes = json_decode($notes, true);
            }

            $link = null;
            $attachments = null;
            $messages = null;
            $result = null;
            $postModelId = $notes['post_model_id'];

            set_time_limit(0);
            date_default_timezone_set('UTC');

            /////// CONFIG ///////
            $username = $notes['access']['username'];
            $password = $notes['access']['password'];

            $debug = false;
            $truncatedDebug = false;

            $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
            $ig->login($username, $password);

            if (isset($notes['Text']) && !empty($notes['Text'])) {
                $messages = $notes['Text'];
            }

            $object = null;
            if (isset($notes['Photos']) && is_array($notes['Photos'])) {

                foreach ($notes['Photos'] as $strPhoto) {

                    $arPhoto = json_decode($strPhoto, true);

                    $file_uri = StaticConfig::getDownloadDir(true) . $arPhoto[0]["file_path"];

                    $waitExists = Files::WaitExists([$file_uri]);
                    if ($waitExists == true) {

                        $resizer = new \InstagramAPI\MediaAutoResizer($file_uri);
                        $result = $ig->timeline->uploadPhoto($resizer->getFile(), ['caption' => $messages]);

                        echo "Загрузка картинки IG \n";

                    } else {
                        Logger::error('Ошибка получения фото из telegram', [
                            'notes' => $notes
                        ]);
                        throw new \Exception("Ошибка получения файлов из telegram");

                    }
                }

            }

            /**
             * Обработка прикрепленного видео инстаграм.
             */

            if (isset($notes['Videos']) && is_array($notes['Videos'])) {

                $waitExists = Files::WaitExists($notes['Videos']);
                /**
                 * Ждем, пока исходный файл сохранится на сервере
                 */
                if ($waitExists == true) {


                    /**
                     * Проверяем видео на наличие расширения.
                     * Если оно отсутствует, то проставляем его.
                     */

                    $videoFile = self::checkVideo($notes['Videos'][0]);

                    /**
                     * Сжимаем видео, т.к. инстаграм принимает видео только
                     * от 480 до 720p
                     */

                    $ffmpeg = FFMpeg::create();

                    $video = $ffmpeg->open($videoFile);
                    $video
                        ->filters()
                        ->resize(new Dimension(640, 480))
                        ->synchronize();

                    /**
                     * Для работы конвертера для дева запускать как
                     * new X264('libfdk_aac');
                     *
                     */

                    $format = new X264();

                    //$format = new X264('libfdk_aac');


                    $output =  self::checkPressPath($notes['video_path']);

                    $res = $video->save($format, $output);

                    /**
                     * Ждем, пока будет сохранен сконвертированный файл
                     */
                    $waitExistsPress = Files::WaitExists([$output]);

                    if ($waitExistsPress == true) {
                        /**
                         * Отправляем сжатый файл в инстаграм
                         */
                        $result = $ig->timeline->uploadVideo($output, ['caption' => $messages]);
                    }else {
                        Logger::error('Ошибка сжатия видео из Telegram', [
                            'notes' => $notes
                        ]);
                        throw new \Exception('Ошибка сжатия видео из Telegram');
                    }
                } else {
                    Logger::error('Ошибка получения видео из Telegram', [
                        'notes' => $notes
                    ]);
                    throw new \Exception("Ошибка получения файлов из telegram");
                }
            }

            $SalesBot = new SalesBotApi();

            /** @var Post $post */
            $post = Post::findOne([
                'id' => $postModelId
            ]);

            if ($post) {
                $post->job_status = Post::JOB_STATUS_POSTED;
                $post->job_result = json_encode('posted');
                $post->save(false);
                $data =  [
                    'callback_tlg_message_status' => $post->callback_tlg_message_status
                ];

                $elseData = $data;
                $data['job_status'] = 'POSTED';
                $elseData['job_status'] = 'FAIL';

                $count = Post::find()->where(['OR', $data, $elseData])->count();
                //отправляем в api
                $arParam = ['data' => json_encode($post->toArray()), 'type' => SocialNetworks::IG, 'tid' => 0];
                $SalesBot->newEvent($arParam);

                /**
                 * Отправляем клиенту уведомление об удачной отправке
                 * $notes['response_data'] - содержит в себе:
                 * - id пользователя
                 * - id чата
                 * - id сообщений вида "instagam - ...", которое мы заменяем на "instagam - готово"
                 */

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
                'social' => Post::SOCIAL_IG
            ]);

            if ($jobPost) {
                $jobPost->status = JobPost::JOB_STATUS_POSTED;
                $jobPost->execute_dt = \Carbon\Carbon::now('Europe/London');
                $jobPost->save(false);

                //отправляем в api
                $arParam = ['data' => json_encode($jobPost->getAttributes()), 'type' => SocialNetworks::IG, 'tid' => 0];
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

            Logger::info('Публикация IG завершена');

            $command = 'sudo chown -R apache:apache /var/www/salesbot/vendor/mgp25/instagram-php/sessions';
            echo shell_exec($command);

            return true;

        } catch (\Exception $e) {

            Logger::error('Error jobs IG', [
                'method' => __METHOD__,
                'message' => $e->getMessage()
            ]);

            $SalesBot = new SalesBotApi();

            /** @var Post $post */
            $post = Post::findOne([
                'id' => $postModelId
            ]);

            if ($post) {
                $post->job_status = Post::JOB_STATUS_POSTED;
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
                $arParam = ['data' => json_encode($post->toArray()), 'type' => SocialNetworks::IG, 'tid' => 0];
                $SalesBot->newEvent($arParam);

                $arParam = [
                    'wall_id' => $post->wall_id,
                    'status' => 0
                ];
                $SalesBot->setUserAccountStatus($arParam);
            }

            /** @var JobPost $jobPost */
            $jobPost = JobPost::findOne([
                'post_id' => $postModelId,
                'social' => Post::SOCIAL_IG
            ]);

            if ($jobPost) {
                $jobPost->status = JobPost::JOB_STATUS_FAIL;
                $jobPost->execute_dt = \Carbon\Carbon::now('Europe/London');
                $jobPost->save(false);

                //отправляем в api
                $arParam = ['data' => json_encode($jobPost->getAttributes()), 'type' => SocialNetworks::IG, 'tid' => 0];
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

            $job->sendComplete();
            return "Error jobs IG";
        }
    }

    public static function getExtension($filename) {
        $path_info = pathinfo($filename);
        return $path_info['extension'];
    }


    public static function checkVideo($file){
        if(self::getExtension($file)===null){

            $newFile = $file.'.'.explode("/", mime_content_type($file))[1];

            if (!copy($file, $newFile)) {
                return false;
            }

            $file = $newFile;
        }

        return $file;
    }

    public static function checkPressPath($file){

        if(strlen(array_pop(explode(".", $file)))>4){
            return $file.'.mp4';
        }else{
            return $file;
        }
    }
}