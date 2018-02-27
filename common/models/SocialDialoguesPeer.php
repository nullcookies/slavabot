<?php

namespace common\models;
use common\models\SocialDialoguesSimple;
use common\models\User;
use yii\db\ActiveRecord;
use Carbon\Carbon;
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
class SocialDialoguesPeer extends ActiveRecord
{
    const SOCIAL_VK = "VK"; // Вконтакте
    const SOCIAL_FB = "FB"; // facebook
    const SOCIAL_IG = "IG"; // instagram

    const TYPE_USER = 'user';
    const TYPE_GROUP = 'group';
    const TYPE_CHAT = 'chat';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'social_dialogues_peer';
    }

    public function getDataNotifications()
    {
        return $this->hasMany(SocialDialoguesSimple::className(), [
            'peer_id' => 'peer_id',
        ])            ->where(['type'=>'message'])
            ;
    }

    public function getDataMediaId()
    {
        return $this->hasOne(SocialDialoguesSimple::className(),
            ['peer_id' => 'peer_id'])
            ->orderBy(['id'=>SORT_DESC]);

    }


    public function fields()
    {
        return [
            'id',
            'created_at',
            'avatar',
            'peer_id',
            'title',
            'social',
            'notification' => 'dataNotifications',
            'media_id' => 'dataMediaId'
        ];
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

    public static function savePeer($social, $type, $peerId, $group_access_token, $access_token)
    {
        $model = static::find()
            ->andWhere(['social' => $social, 'type' => $type, 'peer_id' => $peerId])
            ->one();

        if(!$model) {
            $model = new static;
            $model->social = $social;
            $model->type = $type;
            $model->peer_id = $peerId;
        }

        $peer = $model->getVkPeer($peerId, $group_access_token, $access_token);
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
            $type = static::TYPE_CHAT;
        } else {
            //от пользователя
            $type = static::TYPE_USER;
        }

        return $type;
    }

    public function getVkPeer($peerId, $group_access_token, $access_token)
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
            $avatar = $group[0]['photo_100'];
        } elseif($peerId > 2000000000) {
            //из беседы
            $vkUser = new \frontend\controllers\bot\libs\Vk([
                'access_token' => $access_token
            ]);
            $chatId = $peerId - 2000000000;
            $chat = $vkUser->api('messages.getChat', [
                'chat_id' => $chatId,
                'fields' => 'photo_100',
                'lang' => 0
            ]);

            $name = $chat[0]['title'];
            $avatar = $chat[0]['photo_100'];
        } else {
            //от пользователя
            $user = $vk->api('users.get', [
                'user_ids' => $peerId,
                'fields' => 'photo_100',
                'lang' => 0
            ]);

            var_dump($user[0]);

            $name = $user[0]['first_name'].' '.$user[0]['last_name'];
            $avatar = $user[0]['photo_100'];
        }



        $result = ['title' => $name, 'avatar' => $avatar];

        return $result;
    }

    public static function getPeerByID($peerId){
        $peer = static::find()
            ->where(['peer_id' => $peerId])
            ->one();
        return $peer->title;
    }

    public static function getPostsByPeerAction($peerId){
        $comments = SocialDialogues::find()
            ->select(['post_id', 'social', 'account_id'])
            ->where(['peer_id' => $peerId])
            ->andWhere(['is not', 'post_id', null])
            ->distinct()
            ->asArray()
            ->all();
        return $comments;
    }

    public function getMessagesCount(){
        return count($this->dataNotifications);
    }
}
