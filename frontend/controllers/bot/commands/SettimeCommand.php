<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use frontend\controllers\bot\libs\TelegramWrap;
use Libs\SalesBotApi;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class SettimeCommand extends UserCommand
{
    protected $name = 'settime';                      // Your command's name
    protected $description = 'set time'; // Your command description
    protected $usage = '/settime';                    // Usage of your command
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

            $arUpdates = $this->getUpdate()->getRawData();

            if (isset( $telConfig->config['timezones']['buttons'][ $arUpdates['callback_query']['data'] ]['value'] )) {

                $db            = new \Libs\Db();
                $entityManager = $db->GetManager();

                $user = $entityManager->getRepository('Models\Users')->findOneBy([
                    'telegram_id' => $user_id,
                ]);

                if ($user) {
                    $user->SetTimezone( $telConfig->config['timezones']['buttons'][ $arUpdates['callback_query']['data'] ]['value'] );
                    $entityManager->persist($user);
                    $entityManager->flush();

                    //устанавливаем часовой пояс
                    $SalesBot = new SalesBotApi();
                    $arRequest = $SalesBot->setTimezone(['tid' => $user_id, 'timezone' => $telConfig->config['timezones']['buttons'][ $arUpdates['callback_query']['data'] ]['value']]);

                }
            }

            $data = [
                'chat_id' => $chat_id,
                'text' => 'время установлено '.$telConfig->config['timezones']['buttons'][ $arUpdates['callback_query']['data'] ]['label'].' ('.$arUpdates['callback_query']['data'].')'
            ];

            $data = $telConfig->getSettingsKeyboard($data);

            return Request::sendMessage($data);

        } catch (Longman\TelegramBot\Exception\TelegramException $e) {
            $this->conversation->cancel();
        }
    }

}