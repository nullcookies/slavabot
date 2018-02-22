<?php
/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 12.01.2018
 * Time: 09:16
 *
 * Редактирование сообщения отправленного пользователю.
 */

namespace common\commands\command;
use trntv\bus\interfaces\SelfHandlingCommand;
use yii\base\Object as BaseObject;
use frontend\controllers\bot\Bot;

use Longman\TelegramBot\Request;


class EditTelegramNotificationCommand extends BaseObject implements SelfHandlingCommand
{
    /**
     * $data обязательно содержит:
     *
     * 'chat_id', 'user_id' - идентификаторы диалога
     * 'message_id' - id редактируемого сообщения
     * 'text' - текст, которым мы заменим существующий
     *
     */

    public $data;

    protected function GetCommand()
    {
        $bot = new Bot();
        $telegram = $bot->GetTelegram();
        return $telegram->getCommandObject('notification');
    }

    public function handle($command)
    {
        $data = $command->data;

        try {
            $this->GetCommand();
            $result = Request::editMessageText($data);

            return $result;
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}