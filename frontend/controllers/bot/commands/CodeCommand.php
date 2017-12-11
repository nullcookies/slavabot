<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\MessageEntity;
use Longman\TelegramBot\Request;
use Symfony\Component\Yaml\Yaml;
use Libs\SalesBotApi;

class CodeCommand extends UserCommand
{
    protected $name = 'code';                      // Your command's name
    protected $description = 'Проверка кода'; // Your command description
    protected $usage = '/code';                    // Usage of your command
    protected $version = '1.0.0';
    protected $need_mysql = true;
    protected $conversation;

    public function execute()
    {

        $message = $this->getMessage();

        $text = trim($message->getText(true));


        $chat = $message->getChat();
        $user = $message->getFrom();


        $chat_id = $chat->getId();
        $user_id = $user->getId();

        try {
            $data = [
                'chat_id' => $chat_id,
            ];
            if ($chat->isGroupChat() || $chat->isSuperGroup()) {
                //reply to message id is applied by default
                //Force reply is applied by default so it can work with privacy on
                $data['reply_markup'] = Keyboard::forceReply(['selective' => true]);
            }

            /**
             * Start conversation
             */
            $this->conversation = new Conversation($user_id, $chat_id, $this->getName());


            $result = Request::emptyResponse();

            if ($text === '') {
//            $notes['state'] = 0;
                $this->conversation->update();
                $data['text'] = 'Code: ';

                $result = Request::sendMessage($data);

            } else {

                //отправляем запрос на отправку проверочного кода
                $SalesBot = new SalesBotApi();

                //достаем связку id_telegramm - email
                //сохраняли при отправки письма скодом
                $db = new \Libs\Db();
                $entityManager = $db->GetManager();

                $user = $entityManager->getRepository('Models\Users')->findOneBy([
                    'telegram_id'=>$user_id
                ]);

                if ( $user ) {

                    if ( $SalesBot->authTelegram(
                        [
                        'login' => $user->getEmail(),
                        'code' => $text,
                        'tid' => $user_id
                        ]
                    )) {
                        //письмо с кодом успешно отправленно
                        $data['text'] = "Пользователь активирован.";

                    } else {
                        //письмо не отправленно, пользователь не найден
                        $data['text'] = "Проверочный код не верный.";
                    }


                } else {
                     //пользователь не найден
                    $data['text'] = "Пользователь с таким кодом активации не найден.".$user_id;
                }


                $this->conversation->stop();
                $result = Request::sendMessage($data);
            }


            return $result;        // Send message!
        } catch (\Exception $e) {
            $this->conversation->stop();
        }
    }
}