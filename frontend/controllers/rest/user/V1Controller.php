<?php
namespace frontend\controllers\rest\user;

use common\models\Accounts;
use common\models\User;
use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use frontend\models\UserConfig;



class V1Controller extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => [
                    'send-password',
                    'auth-telegram'
                ],
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => [
                            'send-password',
                            'auth-telegram'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'send-password' => ['post'],
                    'auth-telegram' => ['post']
                ],
            ],
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                'only' => [
                    'send-password',
                    'auth-telegram'
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

    /**
     * Отправка письма с кодом для интеграции с ботом
     *
     * @return array
     */

    public function actionSendPassword(){

        $login = \Yii::$app->request->post('login');

        if(!$login){
            return [
                'status' => false,
                'error' => 'Login error!'
            ];
        }

        $user = User::findByEmail($login);

        if(!$user){
            return [
                'status' => false,
                'error' => 'User not found!'
            ];
        }

        $send = User::SendTemporaryPassword($user->id);

        if(!$send['mail']){
            return [
                'status' => false,
                'error' => 'Sanding Mail server error!'
            ];
        }

        if(!$send['user']){
            return [
                'status' => false,
                'error' => 'Code saving server error!'
            ];
        }

        return [
            'status' => true,
        ];
    }

    /**
     * Авторизация по паре логин + код,
     * Привязка учетки пользователя к telegram id
     * Удаление кода
     *
     * @return array
     */

    public function actionAuthTelegram(){
        $login = \Yii::$app->request->post('login');
        $code = \Yii::$app->request->post('code');
        $telegram_id = \Yii::$app->request->post('tid');

        if(!$login){
            return [
                'status' => false,
                'error' => 'Login error!'
            ];
        }

        if(!$code) {
            return [
                'status' => false,
                'error' => 'Code error!'
            ];
        }

        if(!$telegram_id || (int)$telegram_id==0){
            return [
                'status' => false,
                'error' => 'Telegram ID error!'
            ];
        }

        $user = User::findByEmail($login);

        if(!$user){
            return [
                'status' => false,
                'error' => 'User not found!'
            ];
        }

        if(!$user->validateCode($code)){
            return [
                'status' => false,
                'error' => 'Incorrect code'
            ];
        }

        return [
            'status' => true,
            'telegram_id' => UserConfig::saveTelegramID($user->id, $telegram_id) ? (int)$telegram_id : 'error!',
            'clear_code' => User::clearCode($user->id)
        ];
    }


}