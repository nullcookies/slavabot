<?php

namespace common\models;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "social_dialogues".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $social
 * @property string $type
 * @property integer $peer_id
 * @property integer $message_id
 * @property integer $edited
 * @property integer $direction
 * @property string $text
 * @property string $message
 * @property string $attaches
 * @property integer $created_at
 */
class SocialDialoguesSimple extends ActiveRecord
{
    const SOCIAL_VK = "VK"; // Вконтакте
    const SOCIAL_FB = "FB"; // facebook
    const SOCIAL_IG = "IG"; // instagram

    const TYPE_MESSAGE = 'message';

    const DIRECTION_INBOX = 1;
    const DIRECTION_OUTBOX = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'social_dialogues';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'peer_id', 'text', 'type', 'social', 'message'], 'required'],
            [['user_id', 'peer_id', 'message_id', 'edited', 'direction'], 'integer'],
            [['message', 'text', 'type', 'attaches'], 'string'],
            [['social'], 'string', 'max' => 2],
            [['created_at'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'peer_id' => 'Peer ID',
            'text' => 'Text',
            'message_id' => 'Message ID',
            'edited' => 'Edited',
            'direction' => 'Direction',
            'social' => 'Social',
            'type' => 'Type',
            'message' => 'Message',
            'created_at' => 'Created At'
        ];
    }

    public function fields()
    {
        return [
            'id',
            'social',
            'message'=>'text',
            'created_at',
            'direction',
            'info' => 'message'
        ];
    }

}
