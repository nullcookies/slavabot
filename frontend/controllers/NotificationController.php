<?php

namespace frontend\controllers;

use common\models\Notification;
use common\models\SocialDialogues;
use common\models\SocialDialoguesPeer;
use Yii;
use yii\db\Expression;
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
                    'notifications',
                    'user-notifications'
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
    $subQuery = SocialDialogues::find()
            ->select(new Expression('max(sd.id)'))
            ->from(['sd' => SocialDialogues::tableName()])
            ->where(
                ['and',
                    ['sd.peer_id' => new Expression('social_dialogues.peer_id')],
                    ['user_id' => \Yii::$app->user->identity->id]
                ]);

        $query = SocialDialogues::find()
            ->where(['in', 'id', $subQuery])
            ->orderBy(['id' => SORT_DESC]);
        $models = $query->all();

        return $models;

    }

    public function actionUserNotifications()
    {
        return SocialDialoguesPeer::find()
            ->where(['id'=> Yii::$app->request->post('id')])
            ->all();
    }


}