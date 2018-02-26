<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "social_dialogues_post".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $account_id
 * @property string $social
 * @property string $post_id
 * @property integer $peer_id
 * @property string $text
 * @property string $attaches
 * @property integer $edited
 * @property string $hash
 * @property integer $related_post_id
 * @property string $created_at
 */
class SocialDialoguesPost extends \yii\db\ActiveRecord
{
    const SOCIAL_VK = "VK"; // Вконтакте
    const SOCIAL_FB = "FB"; // facebook
    const SOCIAL_IG = "IG"; // instagram

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'social_dialogues_post';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'account_id', 'social', 'post_id', 'peer_id', 'text'], 'required'],
            [['user_id', 'peer_id', 'edited', 'related_post_id'], 'integer'],
            [['text', 'attaches'], 'string'],
            [['created_at'], 'safe'],
            [['account_id', 'social', 'post_id', 'hash'], 'string', 'max' => 255],
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
            'account_id' => 'Account ID',
            'social' => 'Social',
            'post_id' => 'Post ID',
            'peer_id' => 'Peer ID',
            'text' => 'Text',
            'attaches' => 'Attaches',
            'edited' => 'Edited',
            'hash' => 'Hash',
            'related_post_id' => 'Related Post ID',
            'created_at' => 'Created At',
        ];
    }
}
