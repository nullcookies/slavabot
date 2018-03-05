<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "notifications_texts".
 *
 * @property integer $id
 * @property integer $type
 * @property string $text
 */
class NotificationsTexts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notifications_texts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'text'], 'required'],
            [['type'], 'integer'],
            [['text'], 'string', 'max' => 10000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'text' => 'Text',
        ];
    }
}
