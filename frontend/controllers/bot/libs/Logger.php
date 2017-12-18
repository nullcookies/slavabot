<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 08.12.2017
 * Time: 11:55
 */

namespace frontend\controllers\bot\libs;


class Logger
{
    public static function logger($filename = '')
    {
        $filename = $filename ? $filename : 'logs';

        $path = \Yii::getAlias('@frontend') . '/runtime/logs/' . /*date('Y.m.d') . '/' . */$filename . '.log';

        $logger = new \Monolog\Logger($filename);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($path));
        return $logger;
    }

    public static function error($message, $context = [], $filename = '')
    {
        $logger = self::logger('errors');
        $logger->addRecord(\Monolog\Logger::ERROR, $message, $context);
    }

    public static function info($message, $context = [], $filename = '')
    {
        $logger = self::logger('info');
        $logger->addRecord(\Monolog\Logger::INFO, $message, $context);
    }

}