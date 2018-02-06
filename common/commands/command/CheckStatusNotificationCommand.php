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

    protected function GetCommand()
    {
        $bot = new Bot();
        $telegram = $bot->GetTelegram();
        return $telegram->getCommandObject('notification');
    }

    public function handle($command)
    {

        $data = $command->data;

        $this->GetCommand();

        $data['status'] = 'POSTED';

        $chat = Post::find()->where($data)->one()['internal_uid'];

        $user = $this->getUserCredentialsBySocial($chat, SocialNetworks::VK);

        if (isset($user['wall_id'])) {
            $arr[]='vk';
        }

        $user = $this->getUserCredentialsBySocial($chat, SocialNetworks::FB);

        if ($user['page_id']) {
            $arr[]='fb';
        }

        $user = $this->getUserCredentialsBySocial($chat, SocialNetworks::IG);

        if ($user) {
            $arr[]='ig';
        }

        if(Post::find()->where($data)->count()==count($arr)){
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