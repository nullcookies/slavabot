<?php

namespace common\models;

use yii\base\Model;

class Vk extends Model
{
    public $id;
    public $login;
    public $password;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['id', 'integer'],
            ['login', 'required'],
            ['password', 'required']
        ];
    }
}
