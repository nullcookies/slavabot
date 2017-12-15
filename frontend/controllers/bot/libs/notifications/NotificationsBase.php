<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 05.12.2017
 */

namespace frontend\controllers\bot\libs\notifications;

use frontend\controllers\bot\Bot;
use frontend\controllers\bot\libs\SalesBotApi;
use Longman\TelegramBot\Commands\UserCommands\NotificationCommand;
use Longman\TelegramBot\Exception\TelegramException;

class NotificationsBase
{
    const LIFETIME = 600; // уведомления 10 мин давности
    const DELAY = 60;

    protected $salesBot;
    protected $command;
    protected $manager;

    public function __construct()
    {
        $this->salesBot = new SalesBotApi();
    }

    /**
     * @return \Longman\TelegramBot\Commands\UserCommands\NotificationCommand
     */
    protected function GetCommand()
    {
        $bot = new Bot();
        $telegram = $bot->GetTelegram();
        return $telegram->getCommandObject('notification');
    }

    /**
     * Уведомление в телеграм
     *
     * @param array $_params
     * @throws TelegramException
     * @throws \Exception
     */
    protected function notify($_params = [])
    {
        if(empty($_params['tid']) || empty($_params['message'])) {
            throw new \Exception('Отсутствуют параметры telegram_id или message');
        }

        if(($command = $this->GetCommand()) instanceof NotificationCommand) {
            $command->prepareParams($_params);
            $result = $command->execute();
        }
    }

    /**
     * Сервисная функция для чистки таблицы от переполнения
     */
    protected function service()
    {
        $number = rand(0, 100);

        if($number == 10) {

            try {
                $sql = sprintf('delete from table_notifications where created_at<now() - interval 10 day');
                $statement = $this->manager->getConnection()->prepare($sql);
                $statement->execute();
            }
            catch (\Exception $e) {
                echo  $e->getMessage();
            }

        }

    }

    /**
     * Очистка текста от лишних символов
     * @return string
     */
    protected function clearText($text)
    {
        $text = preg_replace('/(\[([^\]]+)\])/U', '', $text);
        $text = preg_replace('/[^a-zA-Zа-яА-Я0-9 -@#]/ui', '', $text);
        //$text = mb_substr($text, 0, 30);
        $text = trim($text);
        return $text;
    }

}