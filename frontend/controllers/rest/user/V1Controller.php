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
use yii\web\HttpException;



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
                    'auth-telegram' => ['post'],
                    'get-user-email' => ['post'],
                    'clear-telegram' => ['post']
                ],
            ],
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                'only' => [
                    'send-password',
                    'auth-telegram',
                    'set-time-zone',
                    'get-time-zone',
                    'get-user-email',
                    'clear-telegram'
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

//        \Yii::$app->response->setStatusCode(403);
//        return false;

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

    /**
     * Установка пользователю часового пояса
     */

    public function actionSetTimeZone(){

        $telegram_id = \Yii::$app->request->post('tid');
        $timezone = \Yii::$app->request->post('timezone');

        if(!$timezone){
            return [
                'status' => false,
                'error' => 'Timezone error!'
            ];
        }

        if(!$telegram_id || (int)$telegram_id==0){
            return [
                'status' => false,
                'error' => 'Telegram ID error!'
            ];
        }

        $user = User::findByTIG($telegram_id);

        if(!$user){
            return [
                'status' => false,
                'error' => 'User not found!'
            ];
        }

        $result = UserConfig::saveTimezone($user->id, $timezone);

        if($result){
            return [
                'status' => $result,
                'old_timezone' => $user->timezone,
                'new_timezone' => $timezone,
            ];
        }else{
            return [
                'status' => $result,
                'error' => 'Server error!'
            ];
        }

    }

    /**
     * Получение пользователю часового пояса
     */

    public function actionGetTimeZone(){

        $telegram_id = \Yii::$app->request->post('tid');

        if(!$telegram_id || (int)$telegram_id==0){
            return [
                'status' => false,
                'error' => 'Telegram ID error!'
            ];
        }

        $user = User::findByTIG($telegram_id);

        if(!$user){
            return [
                'status' => false,
                'error' => 'User not found!'
            ];
        }

        return [
            'status' => true,
            'timezone' => $user->timezone,
        ];

    }

    /**
     * Получение пользователю часового пояса
     */

    public function actionGetUserEmail(){

        $telegram_id = \Yii::$app->request->post('tid');

        if(!$telegram_id || (int)$telegram_id==0){
            return [
                'status' => false,
                'error' => 'Telegram ID error!'
            ];
        }

        $user = User::findByTIG($telegram_id);

        if(!$user){
            return [
                'status' => false,
                'error' => 'User not found!'
            ];
        }

        return [
            'status' => true,
            'email' => $user->email,
        ];

    }

    public function actionClearTelegram(){
        $telegram_id = \Yii::$app->request->post('tid');

        if(!$telegram_id || (int)$telegram_id==0){
            return [
                'status' => false,
                'error' => 'Telegram ID error!'
            ];
        }

        $status = UserConfig::clearTelegramID($telegram_id);

        return [
            'status' => $status
        ];
    }


}