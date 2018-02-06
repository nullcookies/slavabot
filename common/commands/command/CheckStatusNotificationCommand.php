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
use common\models\Post;
use trntv\bus\interfaces\SelfHandlingCommand;
use yii\base\Object as BaseObject;
use frontend\controllers\bot\Bot;

use Longman\TelegramBot\Request;


class CheckStatusNotificationCommand extends BaseObject implements SelfHandlingCommand
{
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
        $updData = $data;

        $updData['job_status'] = 'POSTED';

        if(Post::find()->where($data)->count()==Post::find()->where($updData)->count()){
            try {
                $this->GetCommand();
                $chat = Post::find()->where($updData)->one()['internal_uid'];

                $result = Request::sendMessage([
                    'chat_id' => $chat,
                    'user_id' => $chat,
                    'text' => 'Отправьте сообщение для публикации'
                ]);

                return $result;
            }
            catch (\Exception $e) {
                return $e->getMessage();
            }
        }

    }
}