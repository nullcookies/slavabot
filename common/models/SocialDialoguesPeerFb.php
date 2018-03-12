<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 01.03.18
 * Time: 15:28
 */

namespace common\models;


class SocialDialoguesPeerFb extends SocialDialoguesPeer
{
    public static function saveFbPeer($peerId, $title, $avatar, $psid = null)
    {
        $social = static::SOCIAL_FB;
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
        $model->psid = $psid;

        if(!$model->save(false)) {
            var_dump($model->errors);
        }

        return $model;
    }
}