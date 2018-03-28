<?php
namespace frontend\controllers;

use common\models\ACity;
use common\models\ACountry;
use common\models\ARegion;
use common\models\FavoritesPosts;
use common\models\Reports;
use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use common\models\Location;
use common\models\Category;
use common\models\Priority;
use common\models\Theme;
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
                    'savefilter' => ['post'],
                    'getpost' => ['post']
                ],
            ],
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                'only' => ['list', 'newfilter', 'filter', 'filters', 'detail', 'getpost', 'contacts'],
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
            'user' => Yii::$app->user->identity,
            'owned' => FavoritesPosts::GetPostsIDByUser(),
            'webhooks' => Webhooks::getWebHooks(),
        );
    }

    public function actionContacts()
    {
        return array(
            'user' => Yii::$app->user->identity,
            'owned' => FavoritesPosts::GetPostsIDByUser(),
            'webhooks' => Webhooks::getWebHooks(true),
        );
    }

    public function actionDetail()
    {
        return array(
            'user' => Yii::$app->user->identity,
            'webhooks' => Webhooks::getDetail(),
        );
    }

    public function actionGetPost()
    {
        return FavoritesPosts::GetPost(Yii::$app->request->post('id'));
    }

    public function actionDropPost()
    {
        return FavoritesPosts::DropPost(Yii::$app->request->post('id'));
    }

    public function actionDropFilter()
    {

        return Filters::DropFilter(Yii::$app->request->post('id'));
    }

    public function actionFilter()
    {
        return array(
            'user' => Yii::$app->user->identity,
            'owned' => FavoritesPosts::GetPostsIDByUser(),
            'filter' => Filters::getFilter(Yii::$app->request->get('id')),
            'location'  =>  ACity::find()->where([
                '>', 'aid', 0
            ])->asArray()->all(),
            'countries' => ACountry::find()->where([
                '>', 'aid', 0
            ])->asArray()->all(),
            'regions' => ARegion::find()->where([
                '>', 'aid', 0
            ])->asArray()->all(),
            'theme'     =>  Reports::getActive()
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
