<?php
/**
 * Created by PhpStorm.
 * User: shakinm@gmail.com
 * Date: 17.10.2017
 * Time: 20:54
 */

namespace frontend\controllers\bot;

//require_once __DIR__ . '/../libs/bootstrap.php';

use Longman\TelegramBot\Telegram;

class BotSet extends Bot
{
    public function Run () {

        try {
            // Create Telegram API object
            $telegram = new  Telegram($this->bot_api_key, $this->bot_username);

            // Set webhook
            $result = $telegram->setWebhook($this->hook_url, ['certificate' => $this->cert]);
            if ($result->isOk()) {
                return $result->getDescription();
            }
        } catch (Longman\TelegramBot\Exception\TelegramException $e) {
            // log telegram errors
            // echo $e->getMessage();
        }
    }
}