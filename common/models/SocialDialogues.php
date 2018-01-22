<?php

namespace common\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "social_dialogues".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $social
 * @property string $type
 * @property integer $peer_id
 * @property string $peer_title
 * @property string $message
 * @property integer $created_at
 */
class SocialDialogues extends ActiveRecord
{
    const SOCIAL_VK = "VK"; // Вконтакте
    const SOCIAL_FB = "FB"; // facebook
    const SOCIAL_IG = "IG";   // instagram

    const TYPE_MESSAGE = 'message';

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
            [['user_id', 'peer_id', 'peer_title', 'type', 'social', 'message'], 'required'],
            [['user_id', 'peer_id'], 'integer'],
            [['message', 'peer_title', 'type'], 'string'],
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
            'peer_title' => 'Peer Title',
            'social' => 'Social',
            'type' => 'Type',
            'message' => 'Message',
            'created_at' => 'Created At'
        ];
    }

    public function getUserValue()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public static function saveMessage($userId, $social, $type, $message)
    {
        var_dump($message);

        $model = new static();
        $model->user_id = $userId;
        $model->social = $social;
        $model->type = $type;

        $model->peer_id = $message[3];
        $model->peer_title = $model->getPeer($message);

        $model->message = json_encode($message);
        $model->created_at = time();

        if(!$model->save()) {
            var_dump($model->errors);
        }

        return $model;
    }

    public function getPeer($update)
    {
        $peerId = $update[3];

        $vk = new \frontend\controllers\bot\libs\Vk([]);

        $name = '';
        if($peerId < 0) {
            //от группы
            $group = $vk->api('groups.getById', [
                'group_ids' => [abs($peerId)]
            ]);

            $name = $group[0]['name']."\n";
        } elseif($peerId > 2000000000) {
            //из беседы

        } else {
            //от пользователя
            $user = $vk->api('users.get', [
                'user_ids' => $peerId,
                'name_case' => 'gen'
            ]);

            $name = $user[0]['first_name'].' '.$user[0]['last_name']."\n";
        }

        return $name;
    }

    public function parseVkMessage()
    {
        $update = $this->message;

        $message = 'message_id: '.$update[1]."\n";
        $message .= 'flags: '.$update[2]."\n";

        $message .= 'peer_id: '.$update[3]."\n";

        $message .= $this->getPeer($update);

        $message .= 'timestamp: '.$update[4]."\n";
        $message .= 'text: '.$update[5]."\n";

        $message .= $this->getAttachments($update);

        return $message;
    }

    protected function getAttachments($update)
    {
        $attachments = $update[6];

        $message = 'attachments: '.json_encode($update[6])."\n";
        $message .= 'attachments: '."\n";

        $attachIsset = true;
        $i = 0;
        while($attachIsset == true) {
            ++$i;
            $attachCount = "attach{$i}";
            $typeName = $attachCount . '_type';
            if(isset($attachments->$typeName)) {
                if($attachments->$typeName == 'photo') {
                    $attachesArray['photo'][$i]['id'] = $attachments->$attachCount;
                }

            } else {
                $attachIsset = false;
            }
        }

        $message .= "\n";
        $message .= 'fwd: '.$update[6]->fwd."\n";
        $message .= 'from: '.$update[6]->from."\n";
        $message .= 'geo: '.$update[6]->geo."\n";
        $message .= 'geo_provider: '.$update[6]->geo_provider."\n";
        $message .= 'title: '.$update[6]->title."\n";
        $message .= 'emoji: '.$update[6]->emoji."\n";
        $message .= 'from_admin: '.$update[6]->from_admin."\n";
        $message .= 'source_act: '.$update[6]->source_act."\n";
        $message .= 'source_mid: '.$update[6]->source_mid."\n";
        $message .= "\n";

        return $message;
    }
}
