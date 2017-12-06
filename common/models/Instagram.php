<?php

namespace common\models;

use yii\base\Model;

class Instagram extends Model
{
    public $id;
    public $type;
    public $data;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['id', 'integer'],
            ['type', 'required'],
            ['data', 'required']
        ];
    }

    public function login($login, $password)
    {
        set_time_limit(0);
        date_default_timezone_set('UTC');

        $debug = false;
        $truncatedDebug = false;

        $ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

        try {
            $loginResponse = $ig->login($login, $password);
            $logoutResponse = $ig->logout();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

}
