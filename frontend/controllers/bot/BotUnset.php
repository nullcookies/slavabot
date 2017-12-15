<?php
/**
 * Created by PhpStorm.
 * User: shakinm@gmail.com
 * Date: 17.10.2017
 * Time: 20:54
 */

namespace frontend\controllers\bot;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\TelegramLog;

class BotUnset extends Bot
{
    public function Run()
    {

        try {
            // Create Telegram API object
            $telegram = new Telegram($this->bot_api_key, $this->bot_username);
            // Delete webhook
            $result = $telegram->deleteWebhook();
            if ($result->isOk()) {
                echo $result->getDescription();
            }
        } catch (TelegramException $e) {
            TelegramLog::error($e->getMessage());
        }
    }
}