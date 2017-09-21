<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
//    'as beforeRequest' => [  //if guest user access site so, redirect to login page.
//        'class' => 'yii\filters\AccessControl',
//        'rules' => [
//            [
//                'actions' => ['login','signup', 'error'],
//                'allow' => true,
//                'roles' => ['?'],
//            ],
//            [
//                'allow' => true,
//                'roles' => ['@'],
//            ],
//        ],
////        'denyCallback' => function () {
////            return Yii::$app->response->redirect(['site/login']);
////        },
//    ],
    'id' => 'app-frontend',
    'sourceLanguage' => 'en-US',
    'language' => 'ru-RU',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'baseUrl' => '/',
            'csrfParam' => '_csrf-frontend',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'baseUrl' => '/',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '/' => 'site/index',
                'index.php' => 'site/index',
            ],
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@frontend/messages',
                    'sourceLanguage' => 'en',
                    'fileMap' => [
                        'main' => 'main.php',
                    ],
                ],
            ],
        ],

    ],
    'params' => $params,
];

