<?php

namespace frontend\controllers;

use common\models\Notification;
use common\models\SocialDialogues;
use common\models\SocialDialoguesPeer;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use common\models\Accounts;
use common\models\Instagram;
use common\services\social\FacebookService;
use common\services\social\VkService;

use common\commands\command\AddToAccountsCommand;


class NotificationController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only'  => ['help', 'contact'],
                'rules' => [
                    [
                        'actions' => ['notifications'],
                        'allow'   => true,
                        'roles'   => ['?'],
                    ],
                    [
                        'actions' => ['instagram', 'finish-process', 'update-process', 'vk-auth'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'vk-auth' => ['post'],
                ],
            ],
            [
                'class'   => \yii\filters\ContentNegotiator::className(),
                'only'    => [
                    'notifications'
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }


    /**
     * Удаление аккаунта
     *
     * @return false|int
     */

    public function actionNotifications()
    {
        $model = SocialDialogues::find()->all();
        return $model;
    }


}