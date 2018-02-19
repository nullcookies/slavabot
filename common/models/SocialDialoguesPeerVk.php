<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 15.02.18
 * Time: 14:11
 */

namespace common\models;


use yii\helpers\ArrayHelper;

class SocialDialoguesPeerVk extends SocialDialoguesPeer
{
    public static function saveVkPeer($peerId, $title, $avatar, $type)
    {
        $social = static::SOCIAL_VK;

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

    public static function parsePeerInfo($peerId, array $groups, array $profiles)
    {
        $peerType = static::getVkPeerType($peerId);

        $result = [];


        if($peerType == static::TYPE_USER) {
            $indexed = ArrayHelper::index($profiles, 'id');

            $result = [
                'title' => $indexed[abs($peerId)]['first_name'] . ' ' . $indexed[abs($peerId)]['last_name'],
                'avatar' => $indexed[abs($peerId)]['photo_100'],
                'type' => $peerType
            ];
        }

        if($peerType == static::TYPE_GROUP) {
            $indexed = ArrayHelper::index($groups, 'id');

            $result = [
                'title' => $indexed[abs($peerId)]['name'],
                'avatar' => $indexed[abs($peerId)]['photo_100'],
                'type' => $peerType
            ];
        }

        return $result;
    }

}