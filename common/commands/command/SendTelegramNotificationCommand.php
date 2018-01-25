<?php
/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 12.01.2018
 * Time: 09:16
 */

namespace common\commands\command;
use trntv\bus\interfaces\SelfHandlingCommand;
use yii\base\Object as BaseObject;
use frontend\controllers\bot\Bot;

use Longman\TelegramBot\Request;


class SendTelegramNotificationCommand extends BaseObject implements SelfHandlingCommand
{
    public $tid;
    public $text;
    //public $message;

    protected function GetCommand()
    {
        $bot = new Bot();
        $telegram = $bot->GetTelegram();
        return $telegram->getCommandObject('notification');
    }

    public function handle($command)
    {
        $telegram_id = $command->tid;
        $text = $command->text;

        try {
            $this->GetCommand();
            $result = Request::sendMessage([
                'chat_id' => $telegram_id,
                'user_id' => $telegram_id,
                'text' => $text
            ]);
            return $result;
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}