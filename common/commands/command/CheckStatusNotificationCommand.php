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
use frontend\controllers\bot\libs\SalesBotApi;
use frontend\controllers\bot\libs\SocialNetworks;
use trntv\bus\interfaces\SelfHandlingCommand;
use yii\base\Object as BaseObject;
use frontend\controllers\bot\Bot;

use Longman\TelegramBot\Request;


class CheckStatusNotificationCommand extends BaseObject implements SelfHandlingCommand
{
    public $data;
    public $count;
    protected function GetCommand()
    {
        $bot = new Bot();
        $telegram = $bot->GetTelegram();
        return $telegram->getCommandObject('notification');
    }

    public function handle($command)
    {

        $data = $command->data;
        $count = $command->count;
        $this->GetCommand();



        $elseData = $data;
        $data['job_status'] = 'POSTED';
        $elseData['job_status'] = 'FAIL';


        $chat = Post::find()->where(['OR', $data, $elseData])->one()['internal_uid'];

        $user1 = $this->getUserCredentialsBySocial($chat, SocialNetworks::VK);

        if (isset($user1['wall_id'])) {
            $arr[]='vk';
        }

        $user2 = $this->getUserCredentialsBySocial($chat, SocialNetworks::FB);

        if ($user2['page_id']) {
            $arr[]='fb';
        }

        $user3 = $this->getUserCredentialsBySocial($chat, SocialNetworks::IG);

        if ($user3) {
            $arr[]='ig';
        }

        if($count==count($arr)){
            try {

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

    public function getUserCredentialsBySocial($internal_id, $social)
    {
        $SalesBot = new SalesBotApi();
        $arRequest = $SalesBot->getUserAccounts(['tid' => $internal_id]);
        if ($arRequest == false) {
            return null;
        } else {
            return SocialNetworks::getParams($arRequest, $social);
        }
    }
}