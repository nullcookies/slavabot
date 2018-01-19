<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Request;
use frontend\controllers\bot\libs\TelegramWrap;

use Carbon\Carbon;
use common\models\User;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\TelegramLog;

class AddpostCommand extends UserCommand
{
    protected $name = 'addpost';                      // Your command's name
    protected $description = 'Add to post';           // Your command description
    protected $usage = '/addpost';                    // Usage of your command
    protected $version = '1.0.0';
    protected $conversation;

    public function execute()
    {

        //подключаем обертку с настройками
        $telConfig = new TelegramWrap();

        try {

            $message = $this->getMessage() ?: $this->getCallbackQuery()->getMessage();
            $chat = $message->getChat();
            $user = $this->getMessage() ? $message->getFrom() : $this->getCallbackQuery()->getFrom();

            $text = trim($message->getText(true));

            $chat_id = $chat->getId();
            $user_id = $user->getId();


            $data = [
                'chat_id' => $chat_id,
                'user_id' => $user_id
            ];


            $this->conversation = new Conversation($user_id, $chat_id, 'post');

            file_put_contents(\Yii::getAlias('@frontend') . '/runtime/logs/cbb.log',
                json_encode($this->conversation) . "\n", FILE_APPEND);

            $notes = &$this->conversation->notes;
            !is_array($notes) && $notes = [];
            //cache data from the tracking session if any
            $state = 0;
            if (isset($notes['state'])) {
                $state = $notes['state'];
            }

            $notes['state'] = 1;

            $this->conversation->update();

            $data['text'] = 'Добавьте данные:';



            return Request::sendMessage($data);
        } catch (Longman\TelegramBot\Exception\TelegramException $e) {
            //$this->conversation->cancel();
        }
    }

}