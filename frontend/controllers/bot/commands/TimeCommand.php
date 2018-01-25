<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use common\models\User;
use frontend\controllers\bot\libs\Logger;
use frontend\controllers\bot\libs\TelegramWrap;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\TelegramLog;
use Longman\TelegramBot\Conversation;



class TimeCommand extends UserCommand
{
    protected $name = 'time';                      // Your command's name
    protected $description = 'Часовой пояс'; // Your command description
    protected $usage = '/time';                    // Usage of your command
    protected $version = '1.0.0';
    protected $need_mysql = true;
    protected $conversation;

    public function execute()
    {

        $telConfig = new TelegramWrap();

        $message = $this->getMessage() ?: $this->getCallbackQuery()->getMessage();
        $chat = $message->getChat();
        $user = $this->getMessage() ? $message->getFrom() : $this->getCallbackQuery()->getFrom();

        $text = trim($message->getText(true));

        $chat_id = $chat->getId();
        $user_id = $user->getId();

        try {

            //if ($text === '' || $text === $telConfig->config['buttons']['time']['label']) {

                $user = User::findOne([
                    'telegram_id' => $user_id
                ]);

                Logger::info('User', [
                    'user' => $user->getAttributes()
                ]);

                if ($user) {
                    $user_timezone = $user->timezone;
                } else {
                    $user_timezone = '';
                }

                $inline_keyboard = new InlineKeyboard([]);
                foreach ($telConfig->config['timezones']['buttons'] as $zone => $arButton) {

                    //добавляем галочку активности
                    $button_text = '';
                    if ($arButton['value'] == $user_timezone) {
                        $button_text = $telConfig->config['timezones']['active'] . ' ';
                    }
                    $button_text .= $arButton['label'] . ' (' . $zone . ')';

                    //собираем кнопки
                    $inline_keyboard->addRow(
                        [
                            'text' => $button_text,
                            'callback_data' => $zone
                        ]
                    );
                }

                $inline_keyboard->addRow(
                    [
                        'text' => "Отмена",
                        'callback_data' => $telConfig->config['buttons']['settings']['command']
                    ]
                );


                $data = [
                    'chat_id' => $chat_id,
                    'user_id' => $user_id,
                    'reply_markup' => $inline_keyboard->setSelective(true),
                    'text' => "Выберите часовой пояс:",

                ];

                Request::sendMessage($data);

            //}

        } catch (TelegramException $e) {

            TelegramLog::error($e->getMessage());

            //$this->conversation->cancel();

            $data = [
                'chat_id' => 55302661,
                'user_id' => 55302661,
                'text' => "Ошибка: " . $e->getMessage(),

            ];

            try {
                return Request::sendMessage($data);
            } catch (TelegramException $e) {
                TelegramLog::error($e->getMessage());
            }

        }
    }
}