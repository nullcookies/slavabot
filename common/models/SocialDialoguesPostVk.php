<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 15.02.18
 * Time: 20:16
 */

namespace common\models;


class SocialDialoguesPostVk extends SocialDialoguesPost
{
    public static function newVkPost($userId, $accountId, $postId, $peerId, $text, $attachments, $hash, $relatedPostId = null)
    {
        $social = static::SOCIAL_VK;

        if(!$model = static::findOne([
            'user_id' => $userId,
            'account_id' => $accountId,
            'social' => $social,
            'post_id' => $postId,
        ])) {
            $model = new static;
            $model->user_id = $userId;
            $model->account_id = $accountId;
            $model->social = $social;
            $model->post_id = $postId;
            $model->peer_id = $peerId;
        } else {
            $model->edited = 1;
        }

        $model->text = $text;
        $model->attaches = $attachments;
        $model->hash = $hash;
        $model->related_post_id = $relatedPostId;

        if(!$model->save(false)) {
            var_dump($model->errors);
        }

        return $model;
    }

    public static function getPostHashByAccountId($userId, $accountId)
    {
        $hashes = static::find()
            ->andWhere([
                'user_id' => $userId,
                'account_id' => $accountId,
                'social' => static::SOCIAL_VK,
            ])
            ->select(['hash'])
            ->asArray()
            ->column();

        return $hashes;
    }
}