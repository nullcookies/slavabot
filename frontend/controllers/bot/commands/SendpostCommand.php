<?php

namespace Longman\TelegramBot\Commands\UserCommands;


use Carbon\Carbon;
use Libs\Db;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use Models\Jobs;
use Models\Posts;
use Symfony\Component\Yaml\Yaml;
use Libs\SocialNetworks;
use Libs\SalesBotApi;

class SendpostCommand extends UserCommand
{
    protected $name = 'sendpost';                      // Your command's name
    protected $description = 'A command for test'; // Your command description
    protected $usage = '/sendpost';                    // Usage of your command
    protected $version = '1.0.0';
    protected $conversation;

    public function execute()
    {
        \Libs\Logger::info(__METHOD__);

        $cb = $this->getUpdate()->getCallbackQuery();            // Get Message object
        $user = $cb->getFrom();
        $chat = $cb->getMessage()->getChat();
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        $this->conversation = new Conversation($user_id, $chat_id, 'post');
        $notes = &$this->conversation->notes;

        $this->prepareVkJob($notes, $user_id);
        $this->prepareFbJob($notes, $user_id);
        $this->prepareIgJob($notes, $user_id);

        $mid = $notes['fm']['result']['message_id'];
        $mtext = $notes['state'] !=5 ? "В ближайшее время пoст появится в соц. сетях." : $notes['fm']['result']['text'];
        $data_edit = [
            'chat_id' => $chat_id,
            'user_id' => $user_id,
            'message_id' => $mid,
            'text' => $mtext,

        ];
        Request::editMessageText($data_edit);

        $this->conversation->stop();
    }

    /**
     * @param $notes
     * @param $user_id
     * @return string
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function prepareVkJob($notes, $user_id)
    {
        \Libs\Logger::info('Подготовка данных для ВК', [
            'method' => __METHOD__,
            'notes' => $notes,
            'user_id' => $user_id
        ]);

        $user = $this->getUserCredentialsBySocial($user_id, SocialNetworks::VK);
        $access = $this->getSocialCredentials(SocialNetworks::VK);
        $photos = null;

        \Libs\Logger::info('Пользователь Телеграм', [
            'user' => $user
        ]);

        if (isset($user['wall_id'])) {
            if (!$access) return false;

            $post = new Posts();
            $post->SetExternalId("");
            $post->SetInternalId($user_id);
            $post->SetWallId($user['wall_id']);
            $post->SetCallbackTlgMessageStatus($notes["MsgId"]);
            $post->SetMessage($notes['Text'] ?: "");
            $post->SetPhoto(isset($notes['Photo']) ? $notes['Photo'] : "");
            $post->SetVideo(isset($notes['Video']) ? $notes['Video'] : "");
            $post->SetJobStatus(Posts::JOB_STATUS_QUEUED);
            $post->SetSocial(Posts::SOCIAL_VK);
            $db = new Db();
            $entityManager = $db->GetManager();
            $entityManager->persist($post);
            $entityManager->flush();

            //отправляем в api
            $SalesBot = new \Libs\SalesBotApi();
            $arParam = ['data' => json_encode($post->toArray()),'type' => \Libs\SocialNetworks::VK, 'tid' => 0];
            $SalesBot->newEvent($arParam);

            $arr['access'] = $access;
            $arr['access']['access_token'] = $user['access_token'];
            $arr['page_access_token'] = $user['page_access_token'];
            $arr['wall_id'] = $user['wall_id'];
            $arr['post_model_id'] = $post->GetId();
            $arr['Text'] = $notes['Text'] ?: "";

            file_put_contents(__DIR__.'/../logs/photos.log',$notes['Photo']);
            if (isset($notes['Photo']) && !empty($notes['Photo'])) {
                $p=json_decode($notes['Photo'], true);
                if (isset($p['file_path'])) {
                    $photo=$p;
                }
                else{
                    $photo = end($p);
                }
                $photos[] = __DIR__ . "/../storage/download/" . $photo['file_path'];
                $arr['Photos'] = $photos;
            }
            if (isset($notes['Video']) && !empty($notes['Video'])) {
                $v=json_decode($notes['Photo'], true);
                if (isset($v['file_path'])) {
                    $video=$v;
                }
                $videos[] = __DIR__ . "/../storage/download/" . $video['file_path'];
                $arr['Videos'] = $videos;
            }
            if (isset($notes['Audio']) && !empty($notes['Audio'])) {
                $audio = end(json_decode($notes['Audio'], true));
                $audios[] = __DIR__ . "/../storage/download/" . $audio['file_path'];
                $arr['Audios'] = $audios;
            }



            //todo Добавить проверку типа
            if (isset($notes['schedule_dt']) && !empty($notes['schedule_dt'])) {
                $arr['schedule_dt'] = $notes['schedule_dt'];
                $payload = json_encode($arr);

                $jobs = new Jobs();
                $jobs->SetInternalId($user_id);
                $jobs->SetScheduleDt(Carbon::parse($notes['schedule_dt']));
                $jobs->SetSocial(Posts::SOCIAL_VK);
                $jobs->SetPostId($post->GetId());
                $jobs->SetStatus(Jobs::JOB_STATUS_QUEUED);
                $jobs->SetPayload($payload);
                $db = new Db();
                $entityManager = $db->GetManager();
                $entityManager->merge($jobs);
                $entityManager->flush();

                //отправляем в api
                $arParam = ['data' => json_encode($jobs->toArray()),'type' => \Libs\SocialNetworks::VK, 'tid' => 0];
                $SalesBot->newEvent($arParam);


                return 'cron';
            }

            $client = new \Kicken\Gearman\Client('127.0.0.1:4730');
            $job = $client->submitBackgroundJob('post_vk', json_encode($arr));

            return $job;
        }

    }

    /**
     * @param $notes
     * @param $user_id
     * @return string
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function prepareFbJob($notes, $user_id)
    {

        $user = $this->getUserCredentialsBySocial($user_id, SocialNetworks::FB);
        $access = $this->getSocialCredentials(SocialNetworks::FB);

        if ( $user['page_id'] ) {
            if (!$access) return false;

            $post = new Posts();
            $post->SetExternalId("");
            $post->SetInternalId($user_id);
            $post->SetWallId($user['page_id']);
            $post->SetCallbackTlgMessageStatus($notes["MsgId"]);
            $post->SetMessage($notes['Text'] ?: "");
            $post->SetPhoto(isset($notes['Photo']) ? $notes['Photo'] : "");
            $post->SetVideo(isset($notes['Video']) ? $notes['Video'] : "");
            $post->SetJobStatus(Posts::JOB_STATUS_QUEUED);
            $post->SetSocial(Posts::SOCIAL_FB);
            $db = new Db();
            $entityManager = $db->GetManager();
            $entityManager->persist($post);
            $entityManager->flush();

             //отправляем в api
            $SalesBot = new \Libs\SalesBotApi();
            $arParam = ['data' => json_encode($post->toArray()),'type' => \Libs\SocialNetworks::FB, 'tid' => 0];
            $SalesBot->newEvent($arParam);

            $arr['access'] = $access;
            $arr['page_id'] = $user['page_id'];
            $arr['page_access_token'] = $user['page_access_token'];
            $arr['post_model_id'] = $post->GetId();
            $arr['Text'] = $notes['Text'] ?: "";
            $arr['hostname'] = $this->getHostname();

            $arr = array_merge($arr,$user);

            if (isset($notes['Photo']) && !empty($notes['Photo'])) {
                //todo сделать множественную загрузку
                $arr['Photos'][]=$notes['Photo'];

            }

            if (isset($notes['Video']) && !empty($notes['Video'])) {
                $v=json_decode($notes['Photo'], true);
                if (isset($v['file_path'])) {
                    $video=$v;
                }
                $videos[] = __DIR__ . "/../storage/download/" . $video['file_path'];
                $arr['Videos'] = $videos;
            }
            if (isset($notes['Audio']) && !empty($notes['Audio'])) {
                $audio = end(json_decode($notes['Audio'], true));
                $audios[] = __DIR__ . "/../storage/download/" . $audio['file_path'];
                $arr['Audios'] = $audios;
            }


            //todo Добавить проверку типа
            if (isset($notes['schedule_dt']) && $notes['schedule_dt'] != "") {
                $arr['schedule_dt'] = $notes['schedule_dt'];
                $payload = json_encode($arr);

                $jobs = new Jobs();
                $jobs->SetInternalId($user_id);
                $jobs->SetSocial(Posts::SOCIAL_FB);
                $jobs->SetScheduleDt(Carbon::parse($notes['schedule_dt']));
                $jobs->SetPostId($post->GetId());
                $jobs->SetStatus(Jobs::JOB_STATUS_QUEUED);
                $jobs->SetPayload($payload);
                $entityManager->persist($jobs);
                $entityManager->flush();

                 //отправляем в api
                $arParam = ['data' => json_encode($jobs->toArray()),'type' => \Libs\SocialNetworks::FB, 'tid' => 0];
                $SalesBot->newEvent($arParam);

                return 'cron';

            }


            $client = new \Kicken\Gearman\Client('127.0.0.1:4730');
            $job = $client->submitBackgroundJob('post_fb', json_encode($arr));

            return $job;
        }

    }

    public function prepareIgJob($notes, $user_id)
    {

        $user = $this->getUserCredentialsBySocial($user_id, SocialNetworks::IG);
        $access = $this->getSocialCredentials(SocialNetworks::IG);

        if (!$user && !$access) return false;

            $post = new Posts();
            $post->SetExternalId("");
            $post->SetInternalId($user_id);
            $post->SetWallId($user['username']);
            $post->SetCallbackTlgMessageStatus($notes["MsgId"]);
            $post->SetMessage($notes['Text'] ?: "");
            $post->SetPhoto(isset($notes['Photo']) ? $notes['Photo'] : "");
            $post->SetVideo(isset($notes['Video']) ? $notes['Video'] : "");
            $post->SetJobStatus(Posts::JOB_STATUS_QUEUED);
            $post->SetSocial(Posts::SOCIAL_IG);
            $db = new Db();
            $entityManager = $db->GetManager();
            $entityManager->persist($post);
            $entityManager->flush();

            //отправляем в api
            $SalesBot = new \Libs\SalesBotApi();
            $arParam = ['data' => json_encode($post->toArray()),'type' => \Libs\SocialNetworks::IG, 'tid' => 0];
            $SalesBot->newEvent($arParam);

            $arr['access'] = $user;
            $arr['page_id'] = $user['username'];
            $arr['post_model_id'] = $post->GetId();
            $arr['Text'] = $notes['Text'] ?: "";


            if (isset($notes['Photo']) && !empty($notes['Photo'])) {
                //todo сделать множественную загрузку
                $arr['Photos'][]=$notes['Photo'];
            }

            if (isset($notes['Video']) && !empty($notes['Video'])) {
                $v=json_decode($notes['Photo'], true);
                if (isset($v['file_path'])) {
                    $video=$v;
                }
                $videos[] = __DIR__ . "/../storage/download/" . $video['file_path'];
                $arr['Videos'] = $videos;
            }
            if (isset($notes['Audio']) && !empty($notes['Audio'])) {
                $audio = end(json_decode($notes['Audio'], true));
                $audios[] = __DIR__ . "/../storage/download/" . $audio['file_path'];
                $arr['Audios'] = $audios;
            }


            //todo Добавить проверку типа
            if (isset($notes['schedule_dt']) && $notes['schedule_dt'] != "") {
                $arr['schedule_dt'] = $notes['schedule_dt'];
                $payload = json_encode($arr);

                $jobs = new Jobs();
                $jobs->SetInternalId($user_id);
                $jobs->SetSocial(Posts::SOCIAL_IG);
                $jobs->SetScheduleDt(Carbon::parse($notes['schedule_dt']));
                $jobs->SetPostId($post->GetId());
                $jobs->SetStatus(Jobs::JOB_STATUS_QUEUED);
                $jobs->SetPayload($payload);
                $db = new Db();
                $entityManager = $db->GetManager();
                $entityManager->persist($jobs);
                $entityManager->flush();

                //отправляем в api
                $arParam = ['data' => json_encode($jobs->toArray()),'type' => \Libs\SocialNetworks::IG, 'tid' => 0];
                $SalesBot->newEvent($arParam);

                return 'cron';

            }


            $client = new \Kicken\Gearman\Client('127.0.0.1:4730');
            $job = $client->submitBackgroundJob('post_ig', json_encode($arr));

            return $job;

        //}

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
        if ( $arRequest == false ) {
            return null;
        } else {
            return SocialNetworks::getParams($arRequest,$social);
        }
     }

    /**
     * @param $social
     * @return array|null
     */
    public function getSocialCredentials($social)
    {
        $common = Yaml::parse(file_get_contents(__DIR__ . '/../config/common.yaml'));
        return isset($common['apps'][$social]) ? $common['apps'][$social] : null;
    }

    public function getHostname()
    {
        $common = Yaml::parse(file_get_contents(__DIR__ . '/../config/common.yaml'));
        return isset($common['hostname']) ? $common['hostname'] : $_SERVER['HTTP_ORIGIN'];
    }

}