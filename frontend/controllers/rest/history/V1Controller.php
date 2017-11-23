<?php
namespace frontend\controllers\rest\history;

use common\models\User;
use common\models\History;
use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;



class V1Controller extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => [
                    'new-event'
                ],
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [
                            'new-event'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'new-event' => ['post']
                ],
            ],
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                'only' => [
                    'new-event'
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * Отключаем проверку токена
     *
     * @param \yii\base\Action $action
     * @return bool
     */

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }


    public function isJSON($string) {
        return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string))))) ? true : false;
    }

    public function actionNewEvent(){

        $type = \Yii::$app->request->post('type');
        $data = \Yii::$app->request->post('data');

        if(!$data){
            return [
                'status' => false,
                'error' => 'Data not found in request!'
            ];
        }


        if(!self::isJSON($data)){
            return [
                'status' => false,
                'error' => 'Data is not JSON!'
            ];
        }

        if(!$type){
            return [
                'status' => false,
                'error' => 'Type not found in request!'
            ];
        }

        if($type!='facebook' && $type!='vkontakte' && $type!='instagram'){
            return [
                'status' => false,
                'error' => 'Type error! It must be equals facebook, vkontakte or instagram!'
            ];
        }

        if(History::saveEvent(0, $type, $data)){
            return [
                'status' => true
            ];
        }else{
            return [
                'status' => false,
                'error' => 'Server error!'
            ];
        }


    }


}