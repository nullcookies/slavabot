<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 07.02.18
 * Time: 16:24
 */

namespace common\models;


class SocialDialoguesInstagram extends SocialDialogues
{
    public static function newIgComment($userId, $commentId, $comment, $peerId)
    {
        $social = static::SOCIAL_IG;
        $type = static::TYPE_MESSAGE;

        $model = new static;
        $model->user_id = $userId;
        $model->social = $social;
        $model->type = $type;
        $model->direction = static::DIRECTION_INBOX;
        $model->peer_id = $peerId;
        $model->message_id = $commentId;
        $model->text = $comment;
        $model->message = '';
        $model->attaches = null;

        if(!$model->save(false)) {
            var_dump($model->errors);
        }

        return $model;
    }

    public static function getCommentIdsByUserId($userId)
    {
        $ids = static::find()
            ->andWhere(['user_id' => $userId, 'social' => static::SOCIAL_IG])
            ->select(['message_id'])
            ->asArray()
            ->column();

        return $ids;
    }
}