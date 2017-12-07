<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use common\models\History;


class HistoryController extends Controller
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
                'only' => ['get-list', 'get-planned'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    public function actionGetList()
    {
        return History::getHistory();
    }
    public function actionGetPlanned()
    {
        return History::getPlanned();
    }

    public function actionIndex()
    {
        $this->layout = '@app/views/layouts/simple.php';

        return $this->render('history', []);
    }
}