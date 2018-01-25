<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use frontend\controllers\bot\libs\SalesBotApi;
use frontend\controllers\bot\libs\TelegramWrap;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\TelegramLog;
use common\models\User;


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

        $message = $message = $this->getMessage() ?: $this->getCallbackQuery()->getMessage();
        $chat = $message->getChat();
        $user = $this->getMessage() ? $message->getFrom() : $this->getCallbackQuery()->getFrom();

        $text = trim($message->getText(true));

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

            $current_settings = [];

            try{
                $api = new SalesBotApi();
                $email = $api->getUserEmail(['tid' => $user_id]);

                $current_settings[] = [
                    "title" => "Учетная запись",
                    "command" => "account",
                    "value" => $email,
                ];

            }catch (RequestException $e){
                $current_settings[] = [
                    "title" => "Учетная запись",
                    "command" => "account",
                    "value" => $e->getMessage()
                ];
            }

            try{
                $current_settings[] = [
                    "title" => "Часовой пояс",
                    "command" => "time",
                    "value" => User::findOne(['telegram_id' => $user_id])->timezone
                ];

            }catch (RequestException $e){
                $current_settings[] = [
                    "title" => "Часовой пояс",
                    "command" => "time",
                    "value" => $e->getMessage()
                ];
            }

            $data = [
                'chat_id' => $chat_id,
                'user_id' => $user_id,

            ];

            return $telConfig->getSettingsWindow(
                        $data,
                        'Текущие настройки: ',
                        $current_settings
                    );

        } catch (\Exception $e) {
            TelegramLog::error($e->getMessage());
            $this->conversation->stop();
        }
    }
}