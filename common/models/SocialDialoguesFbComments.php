<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 05.03.18
 * Time: 16:54
 */

namespace common\models;


use frontend\controllers\bot\libs\Logger;

class SocialDialoguesFbComments extends SocialDialogues
{
    public static function newFbComment($userId, $accountId, $mediaId, $commentId, $comment, $attachments, $peerId, $edited, $direction = self::DIRECTION_INBOX)
    {
        $social = static::SOCIAL_FB;
        $type = static::TYPE_COMMENT;

        if($edited == 1) {
            $model = static::findOne([
                'user_id' => $userId,
                'account_id' => $accountId,
                'social' => $social,
                'type' => $type,
                'post_id' => $mediaId,
                'message_id' => $commentId
            ]);
            $model->edited = 1;
        }

        if(!isset($model)) {
            $model = new static;
            $model->user_id = $userId;
            $model->social = $social;
            $model->type = $type;
            $model->account_id = $accountId;
            $model->post_id = $mediaId;
            $model->message_id = $commentId;
            $model->direction = $direction;
            $model->peer_id = $peerId;
        }

        $model->text = $comment;
        $model->attaches = $attachments;

        if(!$model->save()) {
            Logger::info(json_encode($model->errors));
        }

        return $model;
    }
}