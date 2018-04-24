<?php
namespace frontend\controllers;

use common\commands\command\FilterNotificationCommand;
use common\commands\command\GetPostsCommand;
use common\commands\command\SendTelegramNotificationCommand;
use common\models\billing\Tariffs;
use common\models\Filters;
use common\models\User;
use common\models\Webhooks;
use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use common\commands\command\CheckStatusNotificationCommand;

use SoapClient;


class DevController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                //'only' => ['index'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    public function actionIndex(){

        return User::getTariffBalance();
    }
}