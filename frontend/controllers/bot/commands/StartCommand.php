<?php

namespace Longman\TelegramBot\Commands\SystemCommands;


use frontend\controllers\bot\libs\Logger;
use frontend\controllers\bot\libs\SalesBotApi;
use frontend\controllers\bot\libs\TelegramWrap;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;


class StartCommand extends SystemCommand
{
    protected $name = 'start';                      // Your command's name
    protected $description = 'Start command'; // Your command description
    protected $usage = '/start';                    // Usage of your command
    protected $version = '1.0.0';


    public function execute()
    {

        //подключаем обертку с настройками
        $telConfig = new TelegramWrap();

        $message = $this->getMessage();            // Get Message object

        $chat_id = $message->getChat()->getId();   // Get the current Chat ID
        $user_id = $message->getFrom()->getId();

        $SalesBot = new SalesBotApi();
        $arRequest = $SalesBot->getUserAccounts(['tid' => $user_id]);

        $data = ['chat_id' => $chat_id];

        if ( $arRequest == false ) {
            //если пользователь не подключен

            $data_wellcome = $telConfig->getStartWindow($data);

            try {


                $text = trim($message->getText(true));
                //Conversation start
                $this->conversation = new Conversation($user_id, $chat_id, 'email');
                $notes =& $this->conversation->notes;
                !is_array($notes) && ($notes = []);
                //cache data from the tracking session if any

                $state = 0;


                Request::sendMessage($data_wellcome);
                switch ($state) {
                    case 0:
                        if (($text === '') ||
                            ($text == $telConfig->config['buttons']['email']['label'])
                        ) {

                            $notes['state'] = 0;
                            $this->conversation->update();

                            $data = $telConfig->getEmailWindow($data);
                            Request::sendMessage($data);
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

                        $data = $telConfig->getWelcomeWindow($data, 'Аккаунт успешно подключен. ', []);

                        $this->conversation->stop();

                        Request::sendMessage($data);

                        $data_post = $telConfig->getWelcomeWindow($data, 'Разместите свой первый пост через Славабот ', ['settings']);

                        Request::sendMessage($data_post);

                        break;
                }
            } catch (TelegramException $e) {
                Logger::error('StartCommand', [
                    'message' => $e->getMessage()
                ]);
            }

        } else {

            $data = $telConfig->getWelcomeWindow($data, 'Добро пожаловать. Отправьте сообщение для публикации.', ['settings']);

            try {
                Request::sendMessage($data);
            } catch (TelegramException $e) {
                Logger::error('StartCommand', [
                    'message' => $e->getMessage()
                ]);
            }
        }


    }
}