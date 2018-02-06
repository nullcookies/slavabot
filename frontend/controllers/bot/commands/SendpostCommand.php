<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Carbon\Carbon;
use common\models\JobPost;
use common\models\Post;
use common\services\StaticConfig;
use frontend\controllers\bot\libs\jobs\SocialJobs;
use frontend\controllers\bot\libs\Logger;
use frontend\controllers\bot\libs\SalesBotApi;
use frontend\controllers\bot\libs\SocialNetworks;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\Update;


class SendpostCommand extends UserCommand
{
    protected $name = 'sendpost';                      // Your command's name
    protected $description = 'A command for test'; // Your command description
    protected $usage = '/sendpost';                    // Usage of your command
    protected $version = '1.0.0';
    protected $conversation;

    public function execute()
    {
        //\frontend\controllers\bot\libs\Logger::info(__METHOD__);


        $cb = $this->getUpdate()->getCallbackQuery();            // Get Message object
        $user = $cb->getFrom();
        $chat = $cb->getMessage()->getChat();
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        $this->conversation = new Conversation($user_id, $chat_id, 'post');
        $notes = &$this->conversation->notes;

        $mid = $notes['fm']['result']['message_id'];

        $responseData = [
            'chat_id' => $chat_id,
            'user_id' => $user_id,
        ];

        $mtext = "Публикую:\n";


        $data_edit = [
            'chat_id' => $chat_id,
            'user_id' => $user_id,
            'message_id' => $mid,
            'text' => $mtext,

        ];
        Request::editMessageText($data_edit);

        //try{
            $this->prepareVkJob($notes, $user_id, $responseData);
            $this->prepareFbJob($notes, $user_id, $responseData);
            $this->prepareIgJob($notes, $user_id, $responseData);

            $this->conversation->stop();
//        }catch (\Exception $e){
//
//            $data_edit['text'] = 'Ошибка: '.$e->getMessage();
//            Request::editMessageText($data_edit);
//
//        }
        return (new PostCommand($this->telegram,
            new Update(json_decode($this->update->toJson(), true))))->execute(true, '');

    }

    public function executeNow($text, $notes){

        $chat_id = $this->getMessage()->getFrom()->getId();
        $user_id = $this->getMessage()->getFrom()->getId();

        //$mid = $notes['fm']['result']['message_id'];


        $this->prepareVkJob($notes, $user_id);
        $this->prepareFbJob($notes, $user_id);
        $this->prepareIgJob($notes, $user_id);

        //$this->conversation->stop();

        return (new PostCommand($this->telegram,
            new Update(json_decode($this->update->toJson(), true))))->execute(true, $text);

    }


    /**
     * @param $notes
     * @param $user_id
     * @return string
     */
    public function prepareVkJob($notes, $user_id, $responseData = [])
    {

        \frontend\controllers\bot\libs\Logger::info('Подготовка данных для ВК', [
            'method' => __METHOD__,
            'notes' => $notes,
            'user_id' => $user_id
        ]);

        $user = $this->getUserCredentialsBySocial($user_id, SocialNetworks::VK);
        $access = $this->getSocialCredentials(SocialNetworks::VK);
        $photos = null;

        \frontend\controllers\bot\libs\Logger::info('Пользователь Телеграм', [
            'user' => $user
        ]);

        if (isset($user['wall_id'])) {
            if (!$access) {
                return false;
            }

            $post = new Post();
            $post->internal_uid = $user_id;
            $post->wall_id = $user['wall_id'];
            $post->callback_tlg_message_status = $notes["MsgId"];
            $post->message = $notes['Text'] ?: "";
            $post->photo = isset($notes['Photo']) ? $notes['Photo'] : "";
            $post->video = isset($notes['Video']) ? $notes['Video'] : "";
            $post->job_status = Post::JOB_STATUS_QUEUED;
            $post->social = Post::SOCIAL_VK;
            $post->save(false);

            //отправляем в api
            $SalesBot = new SalesBotApi();
            $arParam = ['data' => json_encode($post->getAttributes()), 'type' => SocialNetworks::VK, 'tid' => 0];
            $SalesBot->newEvent($arParam);

            $arr['access'] = $access;
            $arr['access']['access_token'] = $user['access_token'];
            $arr['page_access_token'] = $user['page_access_token'];
            $arr['wall_id'] = $user['wall_id'];
            $arr['post_model_id'] = $post->id;
            $arr['Text'] = $notes['Text'] ?: "";

            Logger::info($notes['Photo']);

            if (isset($notes['Photo']) && !empty($notes['Photo'])) {
                $p = json_decode($notes['Photo'], true);
                if (isset($p['file_path'])) {
                    $photo = $p;
                } else {
                    $photo = end($p);
                }
                $photos[] = StaticConfig::getDownloadDir(true) . $photo['file_path'];
                $arr['Photos'] = $photos;
            }
            if (isset($notes['Video']) && !empty($notes['Video'])) {
                $v = json_decode($notes['Video'], true)[0];
                if (isset($v['file_path'])) {
                    $video = $v;
                }
                $videos[] = StaticConfig::getDownloadDir(true) . $video['file_path'];
                $arr['Videos'] = $videos;
            }
            if (isset($notes['Audio']) && !empty($notes['Audio'])) {
                $audio = end(json_decode($notes['Audio'], true));
                $audios[] = StaticConfig::getDownloadDir(true) . $audio['file_path'];
                $arr['Audios'] = $audios;
            }


            //todo Добавить проверку типа
            if (isset($notes['schedule_dt']) && !empty($notes['schedule_dt'])) {
                $arr['schedule_dt'] = $notes['schedule_dt'];
                $payload = json_encode($arr);

                $jobs = new JobPost();
                $jobs->internal_uid = $user_id;
                $jobs->social = Post::SOCIAL_VK;
                $jobs->schedule_dt = Carbon::parse($notes['schedule_dt']);
                $jobs->post_id = $post->id;
                $jobs->status = JobPost::JOB_STATUS_QUEUED;
                $jobs->payload = $payload;
                $jobs->save(false);

                //отправляем в api
                $arParam = ['data' => json_encode($jobs->getAttributes()), 'type' => SocialNetworks::VK, 'tid' => 0];
                $SalesBot->newEvent($arParam);


                return 'cron';
            }

            $responseData['text'] = "Вконтакте - ...\n";

            $response = [
                'chat_id' => $responseData['chat_id'],
                'user_id' => $responseData['user_id'],
                'message_id' => json_decode(Request::sendMessage($responseData), true)['result']['message_id'],
                'text' => "Вконтакте - готово\n"
            ];

            $arr['response_data'] = $response;

            $client = new \Kicken\Gearman\Client('127.0.0.1:4730');
            $job = $client->submitBackgroundJob(SocialJobs::FUNCTION_VK, json_encode($arr));



            return [
                'ru_name' => 'Вконтакте',
                'status' => true,
                'code' => 200,
                'message' => $job
            ];

        }else{
            return [
                'ru_name' => 'Вконтакте',
                'status' => false,
                'code' => 404,
                'message' => 'no account found'
            ];
        }

    }

    /**
     * @param $notes
     * @param $user_id
     * @return string
     */
    public function prepareFbJob($notes, $user_id, $responseData = [])
    {

        $user = $this->getUserCredentialsBySocial($user_id, SocialNetworks::FB);
        $access = $this->getSocialCredentials(SocialNetworks::FB);

        if ($user['page_id']) {
            if (!$access) {
                return false;
            }

            $post = new Post();
            $post->internal_uid = $user_id;
            $post->wall_id = $user['page_id'];
            $post->callback_tlg_message_status = $notes["MsgId"];
            $post->message = $notes['Text'] ?: "";
            $post->photo = isset($notes['Photo']) ? $notes['Photo'] : "";
            $post->video = isset($notes['Video']) ? $notes['Video'] : "";
            $post->job_status = Post::JOB_STATUS_QUEUED;
            $post->social = Post::SOCIAL_FB;
            $post->save(false);

            //отправляем в api
            $SalesBot = new SalesBotApi();
            $arParam = ['data' => json_encode($post->getAttributes()), 'type' => SocialNetworks::FB, 'tid' => 0];
            $SalesBot->newEvent($arParam);

            $arr['access'] = $access;
            $arr['page_id'] = $user['page_id'];
            $arr['page_access_token'] = $user['page_access_token'];
            $arr['post_model_id'] = $post->id;
            $arr['Text'] = $notes['Text'] ?: "";
            $arr['hostname'] = $this->getHostname();

            $arr = array_merge($arr, $user);

            if (isset($notes['Photo']) && !empty($notes['Photo'])) {
                //todo сделать множественную загрузку
                $arr['Photos'][] = $notes['Photo'];

            }

            if (isset($notes['Video']) && !empty($notes['Video'])) {
                $v = json_decode($notes['Video'], true)[0];
                if (isset($v['file_path'])) {
                    $video = $v;
                }
                $videos[] = StaticConfig::getDownloadDir(true) . $video['file_path'];
                $arr['Videos'] = $videos;
            }
            if (isset($notes['Audio']) && !empty($notes['Audio'])) {
                $audio = end(json_decode($notes['Audio'], true));
                $audios[] = StaticConfig::getDownloadDir(true) . $audio['file_path'];
                $arr['Audios'] = $audios;
            }


            //todo Добавить проверку типа
            if (isset($notes['schedule_dt']) && $notes['schedule_dt'] != "") {
                $arr['schedule_dt'] = $notes['schedule_dt'];
                $payload = json_encode($arr);

                $jobs = new JobPost();
                $jobs->internal_uid = $user_id;
                $jobs->social = Post::SOCIAL_FB;
                $jobs->schedule_dt = Carbon::parse($notes['schedule_dt']);
                $jobs->post_id = $post->id;
                $jobs->status = JobPost::JOB_STATUS_QUEUED;
                $jobs->payload = $payload;
                $jobs->save(false);

                //отправляем в api
                $arParam = ['data' => json_encode($jobs->getAttributes()), 'type' => SocialNetworks::FB, 'tid' => 0];
                $SalesBot->newEvent($arParam);

                return 'cron';

            }

            $responseData['text'] = "Facebook - ...\n";

            $response = [
                'chat_id' => $responseData['chat_id'],
                'user_id' => $responseData['user_id'],
                'message_id' => json_decode(Request::sendMessage($responseData), true)['result']['message_id'],
                'text' => "Facebook - готово\n"
            ];

            $arr['response_data'] = $response;


            $client = new \Kicken\Gearman\Client('127.0.0.1:4730');
            $job = $client->submitBackgroundJob(SocialJobs::FUNCTION_FB, json_encode($arr));

            return [
                'ru_name' => 'Facebook',
                'status' => true,
                'code' => 200,
                'message' => $job
            ];

        }else{
            return [
                'ru_name' => 'Facebook',
                'status' => false,
                'code' => 404,
                'message' => 'no account found'
            ];
        }

    }

    /**
     * @param $notes
     * @param $user_id
     * @return string
     */
    public function prepareIgJob($notes, $user_id, $responseData = [])
    {

        $user = $this->getUserCredentialsBySocial($user_id, SocialNetworks::IG);
        $access = $this->getSocialCredentials(SocialNetworks::IG);

        if (!$user && !$access) {
            return [
                'status' => false,
                'code' => 404,
                'message' => 'no account found'
            ];
        }

        $post = new Post();
        $post->internal_uid = $user_id;
        $post->wall_id = $user['username'];
        $post->callback_tlg_message_status = $notes["MsgId"];
        $post->message = $notes['Text'] ?: "";
        $post->photo = isset($notes['Photo']) ? $notes['Photo'] : "";
        $post->video = isset($notes['Video']) ? $notes['Video'] : "";
        $post->job_status = Post::JOB_STATUS_QUEUED;
        $post->social = Post::SOCIAL_IG;
        $post->save(false);

        //отправляем в api
        $SalesBot = new SalesBotApi();
        $arParam = ['data' => json_encode($post->getAttributes()), 'type' => SocialNetworks::IG, 'tid' => 0];
        $SalesBot->newEvent($arParam);

        $arr['access'] = $user;
        $arr['page_id'] = $user['username'];
        $arr['post_model_id'] = $post->id;
        $arr['Text'] = $notes['Text'] ?: "";
        $arr['video_path'] = '';

        if (isset($notes['Photo']) && !empty($notes['Photo'])) {
            //todo сделать множественную загрузку
            $arr['Photos'][] = $notes['Photo'];
        }

        if (isset($notes['Video']) && !empty($notes['Video'])) {
            $v = json_decode($notes['Video'], true)[0];
            if (isset($v['file_path'])) {
                $video = $v;
            }
            $videos[] = StaticConfig::getDownloadDir(true) . $video['file_path'];
            $arr['Videos'] = $videos;
            $arr['video_path'] = StaticConfig::getDownloadDir(true).'press/'.$video['file_path'];
        }
        if (isset($notes['Audio']) && !empty($notes['Audio'])) {
            $audio = end(json_decode($notes['Audio'], true));
            $audios[] = StaticConfig::getDownloadDir(true) . $audio['file_path'];
            $arr['Audios'] = $audios;
        }


        //todo Добавить проверку типа
        if (isset($notes['schedule_dt']) && $notes['schedule_dt'] != "") {
            $arr['schedule_dt'] = $notes['schedule_dt'];
            $payload = json_encode($arr);

            $jobs = new JobPost();
            $jobs->internal_uid = $user_id;
            $jobs->social = Post::SOCIAL_IG;
            $jobs->schedule_dt = Carbon::parse($notes['schedule_dt']);
            $jobs->post_id = $post->id;
            $jobs->status = JobPost::JOB_STATUS_QUEUED;
            $jobs->payload = $payload;
            $jobs->save(false);

            //отправляем в api
            $arParam = ['data' => json_encode($jobs->getAttributes()), 'type' => SocialNetworks::IG, 'tid' => 0];
            $SalesBot->newEvent($arParam);

            return 'cron';

        }

        if(isset($notes['Photo']) && !empty($notes['Photo'])){
            $responseData['text'] = "Instagram - ...\n";
            $success_text = "Instagram - готово\n";
        }elseif(isset($notes['Video']) && !empty($notes['Video'])){
            $responseData['text'] = "Instagram - ...\n";
            $success_text = "Instagram - готово\n";
        }else{
            $responseData['text'] = "Instagram - отсутствует фото\n";
            $success_text = $responseData['text'];
        }


        $response = [
            'chat_id' => $responseData['chat_id'],
            'user_id' => $responseData['user_id'],
            'message_id' => json_decode(Request::sendMessage($responseData), true)['result']['message_id'],
            'text' => $success_text
        ];

        $arr['response_data'] = $response;

        $client = new \Kicken\Gearman\Client('127.0.0.1:4730');
        $job = $client->submitBackgroundJob(SocialJobs::FUNCTION_IG, json_encode($arr));

        //return $job;

        return [
            'ru_name' => 'Instagram',
            'status' => true,
            'code' => 200,
            'message' => $job
        ];

    }

    /**
     * @param $internal_id
     * @param $social
     * @return array|null
     */
    public function getUserCredentialsBySocial($internal_id, $social)
    {
        $SalesBot = new SalesBotApi();
        $arRequest = $SalesBot->getUserAccounts(['tid' => $internal_id]);
        if ($arRequest == false) {
            return null;
        } else {
            return SocialNetworks::getParams($arRequest, $social);
        }
    }

    /**
     * @param $social
     * @return array|null
     */
    public function getSocialCredentials($social)
    {
        $common = StaticConfig::configBot('common');
        return isset($common['apps'][$social]) ? $common['apps'][$social] : null;
    }

    public function getHostname()
    {
        $common = StaticConfig::configBot('common');
        return isset($common['hostname']) ? $common['hostname'] : $_SERVER['HTTP_ORIGIN'];
    }

}