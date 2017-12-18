<?php
namespace common\services;
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

    static function vk(){
        return self::config('social')['vk'];
    }

    static function botUrl(){
        return self::config('bot_url');
    }
}