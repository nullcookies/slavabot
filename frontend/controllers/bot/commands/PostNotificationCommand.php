<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 04.12.2017
 */

namespace frontend\controllers\bot\commands;
//namespace Longman\TelegramBot\Commands\UserCommands;

use common\models\FavoritesPosts;
use common\models\SocialDialogues;
use common\models\SocialDialoguesPeer;
use common\models\User;
use frontend\controllers\bot\libs\TelegramWrap;
use frontend\controllers\rest\send\V1Controller;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Commands\UserCommands\MainCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Conversation;
use Yii;


class PostNotificationCommand extends UserCommand
{
    protected $name = 'postNotification';
    protected $description = 'Уведомления';
    protected $usage = '/postNotification';
    protected $version = '1.0.0';
    protected $conversation;
    protected $need_mysql = true;
    protected $_params;

    /**
     * Execute command
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute($post_id = 0, $post_url = '', $state = 0, $new = false)
    {
        $chat_id = $this->_params['tid'];
        $message = $this->_params['message'];

        if(!$chat_id){
            try{
                $message = $this->getMessage() ?: $this->getCallbackQuery()->getMessage();
                $chat = $message->getChat();
                $user = $this->getMessage() ? $message->getFrom() : $this->getCallbackQuery()->getFrom();

                $text = trim($message->getText(true));

                $chat_id = $chat->getId();
                $user_id = $user->getId();

            }catch (TelegramException $e) {
                $data = [
                    'chat_id' => $chat_id,
                    'user_id' => $chat_id,
                    'text' => $e->getMessage()
                ];

                Request::sendMessage($data);
            }
        }


        try{
            $this->conversation = new Conversation($chat_id, $chat_id, "postNotification");

            $notes = &$this->conversation->notes;
            !is_array($notes) && $notes = [];
            //cache data from the tracking session if any


            if (isset($notes['state']) && $state==0) {
                $state = $notes['state'];
            }

            switch ($state) {
                case 0:

                    $buttonsArray = [
                        ['text' => 'Перейти к обсуждению', 'url' => $post_url],
                        ['text' => 'В избранное', 'callback_data' => 'getPost_'.$post_id.'_'.urldecode($post_url)]
                    ];

                    $data = [
                        'chat_id' => $chat_id,
                        'user_id' => $chat_id,
                        'text' => $message
                    ];

                    $inline_keyboard = new InlineKeyboard($buttonsArray);

                    $data['reply_markup'] = $inline_keyboard;

                    $message = Request::sendMessage($data);

                    $notes['MsgId'] = $message->getMessageId();
                    $notes['text'] = $text;
                    $this->conversation->update();

                    break;

                case 1:

                        $notes['post'] = $post_id;

                        $this->conversation->update();

                        try{
                            $favorite = FavoritesPosts::GetPostTLG((int)$post_id, $chat_id, true);
                        }catch (TelegramException $e) {
                            $data = [
                                'chat_id' => $chat_id,
                                'user_id' => $chat_id,
                                'text' => $e->getMessage()
                            ];

                            Request::sendMessage($data);
                            $this->conversation->stop();

                            return (new MainCommand($this->telegram,
                                new Update(json_decode($this->update->toJson(), true))))->execute();

                        }

                        if($favorite){
                            $data = [
                                'chat_id' => $chat_id,
                                'user_id' => $chat_id,
                                'text' => $favorite,
                                'message_id' => $message->getMessageId(),
                            ];

                            Request::editMessageText($data);
                        }

                        $data = [
                            'chat_id' => $chat_id,
                            'user_id' => $chat_id,
                            'text' => 'Пост добавлен в избранное',
                            'message_id' => $message->getMessageId(),
                        ];

                        Request::sendMessage($data);


                        //$this->conversation->stop();

                        return (new MainCommand($this->telegram,
                            new Update(json_decode($this->update->toJson(), true))))->execute();
            }


        }catch (TelegramException $e) {
            $data = [
                'chat_id' => $chat_id,
                'user_id' => $chat_id,
                'text' => $e->getMessage()
            ];

            Request::sendMessage($data);
        }
    }

    public function prepareParams($_params = [])
    {
        $this->_params = $_params;
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