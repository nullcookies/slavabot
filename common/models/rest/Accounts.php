<?php

namespace common\models\rest;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use common\models\ExtraPropsBehaviour;
use common\models\User;


class Accounts extends \yii\db\ActiveRecord
{
    const TYPE_VK = 'vkontakte';

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
        $data = json_decode($this->data);

        return [
            'id',
            'user_id' => function(){
                return $this->userValue->id;
            },
            'telegram_id' => function(){
                return $this->userValue->telegram_id;
            },
            'access_token' => function() use ($data) {
                $token = isset($data->access_token)? $data->access_token: null;
                return $token;
            },
            'group_id' => function() use ($data) {
                $id = isset($data->groups->id)? $data->groups->id: null;
                return $id;
            },
            'group_access_token' => function() use ($data) {
                $token = isset($data->groups->access_token)? $data->groups->access_token: null;
                return $token;
            },
            'ts' => function() use ($data) {
                $token = isset($data->groups->ts)? $data->groups->ts: null;
                return $token;
            },
            'key' => function() use ($data) {
                $token = isset($data->groups->key)? $data->groups->key: null;
                return $token;
            },
            'server' => function() use ($data) {
                $token = isset($data->groups->server)? $data->groups->server: null;
                return $token;
            },
        ];
    }

    public static function getVk()
    {
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

    public static function getVkById($id)
    {
        $model = static::find()
            ->where(
                [
                    'id' => $id,
                    'type' => 'vkontakte',
                    'status' => 1,
                    'processed' => 1
                ]
            )
            ->one();

        return $model;
    }

    public static function getByUserId($userId, $type)
    {
        $model = static::find()
            ->where(
                [
                    'user_id' => $userId,
                    'type' => $type,
                    'status' => 1,
                    'processed' => 1
                ]
            )
            ->one();

        return $model;
    }

    /**
     * Проверяет актуальность данных аккаунта
     */
    public function checkAccount($telegram_id, $group_id, $group_access_token)
    {
        //поменяли телеграм
        //поменяли группу
        //сменился групповой ключ доступа

        $data = json_decode($this->data);

        if($data->groups->id != $group_id) {
            return false;
        }

        $result = [
            'telegram_id' => $telegram_id,
            'access_token' => $data->access_token,
            'group_access_token' => $group_access_token
        ];
        if($this->userValue->telegram_id != $telegram_id) {
            $result['telegram_id'] = $this->userValue->telegram_id;
        }
        if($data->groups->access_token != $group_access_token) {
            $result['group_access_token'] = $data->groups->access_token;
        }

        return $result;
    }
}
