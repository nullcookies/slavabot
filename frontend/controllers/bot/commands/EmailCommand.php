<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use common\models\User;
use frontend\controllers\bot\libs\Logger;
use frontend\controllers\bot\libs\SalesBotApi;
use frontend\controllers\bot\libs\TelegramWrap;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\TelegramLog;

class EmailCommand extends UserCommand
{
    protected $name = 'email';                      // Your command's name
    protected $description = 'Проверка логина'; // Your command description
    protected $usage = '/email';                    // Usage of your command
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

        if (
            ($text == $telConfig->config['buttons']['email']['label']) ||
            ($text == $telConfig->config['buttons']['email']['command'])
        ) {
            $state = 0;
            $text = '';
        }

        if ($text == $telConfig->config['buttons']['repeatcode']['label']) {
            $state = 1;
            $notes['state'] = 1;
            $text = '';
        }


        switch ($state) {
            case 0:
                if (($text === '') ||
                    ($text == $telConfig->config['buttons']['email']['label'])
                ) {
                    $notes['state'] = 0;
                    $this->conversation->update();

                    $data = $telConfig->getEmailWindow($data);

                    try {
                        $result = Request::sendMessage($data);
                    } catch (TelegramException $e) {
                        TelegramLog::error($e->getMessage());
                    }
                    break;
                }

                //отправляем запрос на отправку проверочного кода
                $SalesBot = new SalesBotApi();

                $res = $SalesBot->sendPassword(['login' => $text]);

                if ($res['status']) {
                    $notes['email'] = $text;
                } else if($res['error'] == 'server error'){
                    $data = $telConfig->getErrorEmailWindow($data);

                    try {
                        $result = Request::sendMessage($data);
                    } catch (TelegramException $e) {
                        TelegramLog::error($e->getMessage());
                    }

                } else if($res['error']=='User not found!'){

                    $data = $telConfig->getWrongEmailWindow($data, $text);

                    try {
                        $result = Request::sendMessage($data);
                    } catch (TelegramException $e) {
                        TelegramLog::error($e->getMessage());
                    }
                    break;

                }else{
                    $data = $telConfig->getErrorEmailWindow($data);

                    try {
                        $result = Request::sendMessage($data);
                    } catch (TelegramException $e) {
                        TelegramLog::error($e->getMessage());
                    }
                }
                $text = '';
            // no break
            // no break
            case 1:
                if ($text === '') {
                    $notes['state'] = 1;

                    $user = User::findOne([
                        'telegram_id' => $user_id
                    ]);

                    if ($user) {
                        $notes['email'] = $user->email;
                    }
                    $this->conversation->update();

                    $data = $telConfig->getCodeWindow($data, $notes);

                    try {
                        $result = Request::sendMessage($data);
                    } catch (TelegramException $e) {
                        TelegramLog::error($e->getMessage());
                    }
                    break;
                }

                //отправляем запрос на отправку проверочного кода
                $SalesBot = new SalesBotApi();


                if ($SalesBot->authTelegram(
                    [
                        'login' => $notes['email'],
                        'code' => $text,
                        'tid' => $user_id
                    ]
                )) {
                    $notes['state'] = 2;
                    $this->conversation->update();
                } else {
                    //письмо не отправленно, пользователь не найден

                    $data = $telConfig->getCodeWrongWindow($data);
                    try {
                        $result = Request::sendMessage($data);
                    } catch (TelegramException $e) {
                        TelegramLog::error($e->getMessage());
                    }
                    break;
                }

                $text = '';


            // no break
            // no break
            case 2:
                $this->conversation->update();

                $data = $telConfig->getWelcomeWindow($data, 'Аккаунт успешно подключен.', []);

                $this->conversation->stop();

                Request::sendMessage($data);

                return (new PostCommand($this->telegram,
                    new Update(json_decode($this->update->toJson(), true))))->execute(true, 'Разместите свой первый пост через Славабот ');

                break;
        }
        return $result;
    }
}