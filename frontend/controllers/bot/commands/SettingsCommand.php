<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use frontend\controllers\bot\libs\TelegramWrap;
use Libs\SalesBotApi;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;


class SettingsCommand extends UserCommand
{
    protected $name = 'settings';                      // Your command's name
    protected $description = 'Настройка'; // Your command description
    protected $usage = '/settings';                    // Usage of your command
    protected $version = '1.0.0';
    protected $need_mysql = true;
    protected $conversation;

    public function execute()
    {
        //подключаем обертку с настройками
        $telConfig = new TelegramWrap();

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
                $this->conversation->update();


            } else {

                $this->conversation->stop();

            }

            $data = $telConfig->getSettingsWindow($data);
            $result = Request::sendMessage($data);

            return $result;        // Send message!
        } catch (\Exception $e) {
            $this->conversation->stop();
        }
    }
}