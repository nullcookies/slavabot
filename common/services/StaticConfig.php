<?php
namespace common\services;
use Symfony\Component\Yaml\Yaml;
use yii\base\Object;

class StaticConfig
{
    public function config($param)
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/../config/config.yaml'))[$param];
    }

    public function timezones(){
        return self::config('timezones');
    }

    public function facebook(){
        return self::config('social')['facebook'];
    }
}