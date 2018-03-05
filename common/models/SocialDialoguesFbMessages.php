<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 28.02.18
 * Time: 16:11
 */

namespace common\models;


use frontend\controllers\bot\libs\Logger;

class SocialDialoguesFbMessages extends SocialDialogues
{
    public static function newFbMessage($userId, $accountId, $peerId, $messageId, $text, $attaches, $direction = SocialDialogues::DIRECTION_INBOX)
    {
        $model = new static;
        $model->user_id = $userId;
        $model->account_id = $accountId;
        $model->social = static::SOCIAL_FB;
        $model->type = static::TYPE_MESSAGE;
        $model->direction = $direction;
        $model->peer_id = $peerId;
        $model->message_id = $messageId;
        $model->text = $text;
        $model->attaches = $attaches;

        if(!$model->save(false)) {
            Logger::info(json_encode($model->getErrors()));
            var_dump($model->errors);
        }

        return $model;
    }
}