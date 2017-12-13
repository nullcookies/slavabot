<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use frontend\controllers\bot\libs\TelegramWrap;

class CancelpostCommand extends UserCommand
{
    protected $name = 'cancelpost';                      // Your command's name
    protected $description = 'Cancel post'; // Your command description
    protected $usage = '/cancelpost';                    // Usage of your command
    protected $version = '1.0.0';
    protected $conversation;

    public function execute()
    {

        //подключаем обертку с настройками
        $telConfig = new TelegramWrap();

        try {
            $cb = $this->getUpdate()->getCallbackQuery();    // Get Message object

            if (!$cb) {
                $cb = $this->getCallbackQuery();
            }
            if (!$cb) {
                return false;
            }
            $user = $cb->getFrom();
            $chat = $cb->getMessage()->getChat();
            $chat_id = $chat->getId();
            $user_id = $user->getId();

            $this->conversation = new Conversation($user_id, $chat_id, 'post');
            $this->conversation->stop();
            $data = [
                'chat_id' => $chat_id,
            ];

            $data = $telConfig->getPostCancelWindow($data);

            return Request::sendMessage($data);
        } catch (Longman\TelegramBot\Exception\TelegramException $e) {
            $this->conversation->cancel();
        }
    }

}