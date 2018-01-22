<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'encrypter' => [
            'class'=>'\nickcv\encrypter\components\Encrypter',
            'globalPassword'=>'slavabotglobal',
            'iv'=>'slavabotIdVector',
            'useBase64Encoding'=>true,
            'use256BitesEncoding'=>false,
        ],
    ],
];
