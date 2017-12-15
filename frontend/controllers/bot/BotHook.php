<?php
/**
 * Created by PhpStorm.
 * User: shakinm@gmail.com
 * Date: 17.10.2017
 * Time: 20:54
 */

namespace frontend\controllers\bot;

use common\services\StaticConfig;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\TelegramLog;


class BotHook extends Bot
{
    public function Run()
    {
        $settings = StaticConfig::configBot('db');

        $mysql_credentials = [
            'host' => $settings['db']['host'],
            'user' => $settings['db']['user'],
            'password' => $settings['db']['password'],
            'database' => $settings['db']['dbname'],
        ];

        try {
            // Create Telegram API object
            $telegram = new Telegram($this->bot_api_key, $this->bot_username);

            $telegram->addCommandsPaths($this->commands_paths);
            $telegram->enableMySql($mysql_credentials);

            $telegram->setDownloadPath($this->download_dir);
            $telegram->setUploadPath($this->upload_dir);

            // Logging (Error, Debug and Raw Updates)
            \Longman\TelegramBot\TelegramLog::initErrorLog(\Yii::getAlias('@frontend') . "/runtime/logs/{$this->bot_username}_error.log");
            \Longman\TelegramBot\TelegramLog::initDebugLog(\Yii::getAlias('@frontend') . "/runtime/logs/{$this->bot_username}_debug.log");
            \Longman\TelegramBot\TelegramLog::initUpdateLog(\Yii::getAlias('@frontend') . "/runtime/logs/{$this->bot_username}_update.log");

            // Handle telegram webhook request
            $telegram->handle();

        }  catch (TelegramException $e) {
            TelegramLog::error($e->getMessage());
        }
    }
}