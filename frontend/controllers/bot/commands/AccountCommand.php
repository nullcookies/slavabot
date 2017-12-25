<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use common\models\User;
use frontend\controllers\bot\libs\Logger;
use frontend\controllers\bot\libs\SalesBotApi;
use frontend\controllers\bot\libs\TelegramWrap;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\TelegramLog;

class AccountCommand extends UserCommand
{
    protected $name = 'account';                      // Your command's name
    protected $description = 'Проверка логина'; // Your command description
    protected $usage = '/account';                    // Usage of your command
    protected $version = '1.0.0';
    protected $need_mysql = true;
    protected $conversation;

    public function execute()
    {

        //подключаем обертку с настройками
        $telConfig = new TelegramWrap();

        $message = $this->getMessage();
        $chat = $message->getChat();
        $user = $message->getFrom();
        $text = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        //Preparing Response
        $data = ['chat_id' => $chat_id];

        //Conversation start
        $this->conversation = new Conversation($user_id, $chat_id, $this->name);
        $notes =& $this->conversation->notes;
        !is_array($notes) && ($notes = []);
        //cache data from the tracking session if any
        $state = 0;
        if (isset($notes['state'])) {
            $state = $notes['state'];
        }
        try {
            $result = Request::emptyResponse();
        } catch (TelegramException $e) {
            TelegramLog::error($e->getMessage());
        }


        try{
            $api = new SalesBotApi();
            $email = $api->getUserEmail(['tid' => $user_id]);
        }catch (RequestException $e){
            $email = $e->getMessage();
        }

        $data = [
            'chat_id' => $chat_id,
            'user_id' => $user_id,
            'text' => 'Подключен email: '.$email,

        ];

        $data = $telConfig->getAccountSettingsKeyboard($data);


        return Request::sendMessage($data);
    }
}