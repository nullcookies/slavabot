<?php
namespace common\services;
use common\models\NotificationsTexts;
use Symfony\Component\Yaml\Yaml;
use yii\base\Object;

class StaticConfig
{
    
    static function config($param)
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/../config/config.yaml'))[$param];
    }

    static function configBot($name)
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/../config/bot/'.$name.'.yaml'));
    }

    static function timezones(){
        return self::config('timezones');
    }

    static function facebook(){
        return self::config('social')['facebook'];
    }

    static function defaulTariff(){
        return self::config('defaul_tarif');
    }

    static function vk(){
        return self::config('social')['vk'];
    }

    static function postsNotifications(){
        $notifications = self::config('postsNotifications');

        $notifications = NotificationsTexts::find()
            ->where(['type' => 1])
            ->asArray()
            ->all();

        return $notifications[rand(0, count($notifications)-1)]['text'];
    }

    static function botUrl(){
        return self::config('bot_url');
    }

    public static function getDownloadDir($server = false)
    {
        if($server) {
            return \Yii::getAlias('@webroot') . '/storage/download/';
        }
        else {
            return '/storage/download/';
        }
    }

    public static function getUploadDir($server = false)
    {
        if($server) {
            return \Yii::getAlias('@webroot') . '/storage/upload/';
        }
        else {
            return '/storage/upload/';
        }
    }

    public static function ReportsConfig(){
        return self::config('ReportsConfig');
    }

}