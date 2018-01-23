<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "social_dialogues_peer".
 *
 * @property integer $id
 * @property string $social
 * @property string $type
 * @property integer $peer_id
 * @property string $title
 * @property string $avatar
 * @property string $created_at
 */
class SocialDialoguesPeer extends \yii\db\ActiveRecord
{
    const SOCIAL_VK = "VK"; // Вконтакте
    const SOCIAL_FB = "FB"; // facebook
    const SOCIAL_IG = "IG"; // instagram

    const TYPE_USER = 'user';
    const TYPE_GROUP = 'group';
    const TYPE_DIALOG = 'dialog';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'social_dialogues_peer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['social', 'type', 'peer_id', 'title', 'avatar'], 'required'],
            [['peer_id'], 'integer'],
            [['created_at'], 'safe'],
            [['social'], 'string', 'max' => 2],
            [['type', 'title', 'avatar'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'social' => 'Social',
            'type' => 'Type',
            'peer_id' => 'Peer ID',
            'title' => 'Title',
            'avatar' => 'Avatar',
            'created_at' => 'Created At',
        ];
    }

    public static function savePeer($social, $peerId, $group_access_token)
    {
        $type = static::getVkPeerType($peerId);

        $model = static::find()
            ->andWhere(['social' => $social, 'type' => $type, 'peer_id' => $peerId])
            ->one();

        if(!$model) {
            $model = new static;
            $model->social = $social;
            $model->type = $type;
            $model->peer_id = $peerId;
        }

        $peer = $model->getVkPeer($peerId, $group_access_token);
        $model->title = $peer['title'];
        $model->avatar = $peer['avatar'];

        if(!$model->save(false)) {
            var_dump($model->errors);
        }

        return $model;
    }

    public static function getVkPeerType($peerId)
    {
        if($peerId < 0) {
            //от группы
            $type = static::TYPE_GROUP;
        } elseif($peerId > 2000000000) {
            //из беседы
            $type = static::TYPE_DIALOG;
        } else {
            //от пользователя
            $type = static::TYPE_USER;
        }

        return $type;
    }

    public function getVkPeer($peerId, $group_access_token)
    {
        $name = '';
        $avatar = '';
        $vk = new \frontend\controllers\bot\libs\Vk([
            'access_token' => $group_access_token
        ]);

        if($peerId < 0) {
            //от группы
            $group = $vk->api('groups.getById', [
                'group_ids' => [abs($peerId)],
                'lang' => 0
            ]);

            $name = $group[0]['name'];
            $avatar = $group[0]['photo_200'];
        } elseif($peerId > 2000000000) {
            //из беседы

        } else {
            //от пользователя
            $user = $vk->api('users.get', [
                'user_ids' => $peerId,
                'fields' => 'photo_max',
                'lang' => 0
            ]);

            var_dump($user[0]);

            $name = $user[0]['first_name'].' '.$user[0]['last_name'];
            $avatar = $user[0]['photo_max'];
        }



        $result = ['title' => $name, 'avatar' => $avatar];

        return $result;
    }
}
