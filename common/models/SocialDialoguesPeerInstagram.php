<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 07.02.18
 * Time: 16:56
 */

namespace common\models;


class SocialDialoguesPeerInstagram extends SocialDialoguesPeer
{
    public static function saveIgPeer($peerId, $title, $avatar)
    {
        $social = static::SOCIAL_IG;
        $type = static::TYPE_USER;

        $model = static::find()
            ->andWhere(['social' => $social, 'type' => $type, 'peer_id' => $peerId])
            ->one();

        if(!$model) {
            $model = new static;
            $model->social = $social;
            $model->type = $type;
            $model->peer_id = $peerId;
        }

        $model->title = $title;
        $model->avatar = $avatar;

        if(!$model->save(false)) {
            var_dump($model->errors);
        }

        return $model;
    }
}