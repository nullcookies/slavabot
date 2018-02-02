<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Update;
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
            file_put_contents(\Yii::getAlias('@frontend') . '/runtime/logs/cbb.log',
                json_encode($this->conversation) . "\n", FILE_APPEND);

            $notes = &$this->conversation->notes;
            !is_array($notes) && $notes = [];
            Request::deleteMessage([
                'chat_id' => $chat_id,
                'message_id' => $notes['fm']['result']['message_id'],
            ]);

            $this->conversation->stop();
            $data = [
                'chat_id' => $chat_id,
            ];

            return (new PostCommand($this->telegram,
                new Update(json_decode($this->update->toJson(), true))))->execute(true, 'Создание поста отменено. Добавьте новый пост:');

//            $data = $telConfig->getPostCancelWindow($data);
//
//            return Request::sendMessage($data);
        } catch (Longman\TelegramBot\Exception\TelegramException $e) {
            $this->conversation->cancel();
        }
    }

}