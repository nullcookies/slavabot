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
 * @property integer $created_at
 */
class SocialDialogues extends ActiveRecord
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
            [['message', 'text', 'type'], 'string'],
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

    public function getUserValue()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getPeer()
    {
        return $this->hasOne(SocialDialoguesPeer::className(), [
            'social' => 'social',
            'peer_id' => 'peer_id'
        ]);
    }

    public static function saveMessage($userId, $social, $type, array $message)
    {
        //новое сообщение
        if(isset($message[0]) && $message[0] == 4) {
            return static::newMessage($userId, $social, $type, $message);
        }
        //редактированное сообщение
        if(isset($message[0]) && $message[0] == 5) {
            return static::editedMessage($userId, $social, $type, $message);
        }

        return false;
    }

    public static function newMessage($userId, $social, $type, array $message)
    {
        $model = new static;
        $model->user_id = $userId;
        $model->social = $social;
        $model->type = $type;
        $model->direction = $model->getDirection($message[2]);
        $model->peer_id = $message[3];
        $model->message_id = $message[1];
        $model->text = $message[5];

        $model->message = json_encode($message);

        if(!$model->save(false)) {
            var_dump($model->errors);
        }

        return $model;
    }

    public static function editedMessage($userId, $social, $type, array $message)
    {
        $model = static::find()
            ->andWhere([
                'user_id' => $userId,
                'social' => $social,
                'type' => $type,
                'peer_id' => $message[3],
                'message_id' => $message[1],
            ])
            ->one();

        if($model) {
            $model->text = $message[5];
            $model->message = json_encode($message);
            $model->edited = 1;

            if(!$model->save(false)) {
                var_dump($model->errors);
            }

            return $model;
        }

        return false;
    }

    public function getDirection($flags)
    {
        $summands = [];
        foreach([1, 2, 4, 8, 16, 32, 64, 128, 256, 512, 65536] as $number) {
            if ($flags & $number) {
                $summands[] = $number;
            }
        }
        if(in_array(2, $summands)) {
            return static::DIRECTION_OUTBOX;
        }

        return static::DIRECTION_INBOX;
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
