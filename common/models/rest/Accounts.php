<?php

namespace common\models\rest;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use common\models\ExtraPropsBehaviour;
use common\models\User;


class Accounts extends \yii\db\ActiveRecord
{



    public static function tableName()
    {
        return 'social';
    }

    public function rules()
    {
        return [
            [['user_id', 'status', 'processed'], 'integer'],
            [['type', 'data'], 'string']

        ];
    }

    public function getUserValue()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    public function fields()
    {
        return [
            'telegram_id' => function(){
                return $this->userValue->telegram_id;
            },
            'access_token' => function(){
                return json_decode($this->data)->access_token;
            }
        ];
    }

    public static function getVk(){
        $model = static::find()
            ->where(
                [
                    'type' => 'vkontakte',
                    'status' => 1,
                    'processed' => 1
                ]
            )
            ->all();
        return $model;
    }

}
