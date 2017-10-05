<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\Filters;
use common\models\Webhooks;
use yii\web\Response;


/**
 * Site controller
 */
class PotentialController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup', 'repassword', 'newfilter'],
                'rules' => [
                    [
                        'actions' => ['signup', 'repassword'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'newfilter'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['get'],
                    'savefilter' => ['post']
                ],
            ],
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                'only' => ['list', 'newfilter', 'filter', 'filters'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];

    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionList()
    {
        return array(
            'webhooks' => Webhooks::getWebHooks(),
        );
    }

    public function actionFilter()
    {
        return array(
            'filter' => Filters::getFilter(Yii::$app->request->get('id')),
            'webhooks' => Webhooks::getWebHooks(),
        );
    }

    public function actionFilters()
    {
        return Filters::getFilters();
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionNewFilter()
    {
        return Filters::saveFilter(Yii::$app->request->post());
    }

    public function actionUpdateFilter()
    {
        return Filters::UpdateFilter(Yii::$app->request->post());
    }

}
