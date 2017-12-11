<?php
/**
 * Created by PhpStorm.
 * User: shakinm@gmail.com
 * Date: 18.10.2017
 * Time: 10:37
 */

namespace frontend\controllers\bot;

use Longman\TelegramBot\Telegram;
use common\services\StaticConfig;

/**
 * Class Bot
 * @package App
 */
class Bot
{
    protected static $telegram;

    // TODO: Вынести переменные в YAML конфиг

    public $bot_api_key = '475149120:AAEApdOiByJzqvnm0C9FnVdw3Juq6GGVfv0';
    public $bot_username = 'mlgbottest';
    public $commands_paths = [__DIR__ . '/commands'];
    public $hook_url = 'https://tlgbot.mlg.ddemo.ru/hook.php';
    public $cert;
    public $download_dir = __DIR__ . '/storage/download';
    public $upload_dir = __DIR__ . '/storage/upload';

    public function __construct()
    {
        $settings = StaticConfig::configBot('common');

        $this->bot_api_key = $settings['bot']['api_key'];
        $this->bot_username = $settings['bot']['username'];
        $this->hook_url = $settings['bot']['hook_url'];
        $this->cert = $settings['bot']['cert'];

        if (isset($settings['bot']['download_dir'])) {
            $this->download_dir = $settings['bot']['download_dir'];
        }
        if (isset($settings['bot']['upload_dir'])) {
            $this->upload_dir = $settings['bot']['upload_dir'];
        }
    }

    public function GetTelegram()
    {
        if(self::$telegram == null) {
            self::$telegram = new Telegram($this->bot_api_key, $this->bot_username);
        }

        return self::$telegram;
    }

}