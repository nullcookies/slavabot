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

        //try {

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


        $notes['state'] = 0;
        $notes['stage'] = 'added';

        $inline_keyboard = new InlineKeyboard([]);

        $data['reply_markup'] = $inline_keyboard;

        $this->changeFM($notes, $inline_keyboard, $user_id, $chat_id);

        $this->conversation->update();

        $data['text'] = 'Введите текст / Добавьте медиа:';

        $notes['fm'] = Request::sendMessage($data);

    }

    private function changeFM($notes, $inline_keyboard, $user_id, $chat_id, $remove_kb = false)
    {

        $notes=json_decode(json_encode($notes),true);

        $mid = $notes['fm']['result']['message_id'];
        $mtext = $notes['fm']['result']['text'];

        if (!$remove_kb) {
            $data_edit = [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'message_id' => $mid,
                'text' => $mtext,
                'reply_markup' => $inline_keyboard
            ];
        } else {
            $data_edit = [
                'chat_id' => $chat_id,
                'user_id' => $user_id,
                'message_id' => $mid,
                'text' => $mtext,

            ];
        }

        // Try to edit selected message.
        $result = Request::editMessageText($data_edit);
        return $result->getResult();
    }

}