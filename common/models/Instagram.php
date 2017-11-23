<?php

namespace common\models;

use Yii;

class Instagram
{
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
