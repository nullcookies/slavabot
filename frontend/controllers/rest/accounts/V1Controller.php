<?php
namespace frontend\controllers\rest\accounts;

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
use frontend\controllers\VKController;
use Vk;


class V1Controller extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['get-user-accounts', 'set-account-status', 'get-token', 'user-auth'],
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['get-user-accounts', 'set-account-status', 'get-token', 'user-auth'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'get-user-accounts' => ['post'],
                    'set-account-status' => ['post']
                ],
            ],
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                'only' => ['get-user-accounts', 'set-account-status', 'get-token', 'user-auth'],
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

    public function actionGetUserAccounts(){

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
            'telegram_id' => $user->telegram_id,
            'data' => Accounts::getByUser($user->id)
        ];

    }

    /**
     *
     *  Устанавливаем пользователю telegram id по логину-паролю
     *
     *  @return array
     *
     */

    public function actionUserAuth(){

        $login = \Yii::$app->request->post('login');
        $password = \Yii::$app->request->post('password');
        $telegram_id = \Yii::$app->request->post('tig');

        if(!$login){
            return [
                'status' => false,
                'error' => 'Login error!'
            ];
        }

        if(!$password) {
            return [
                'status' => false,
                'error' => 'Password error!'
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

        if(!$user->validatePassword($password)){

            return [
                'status' => false,
                'error' => 'Incorrect password'
            ];

        }

        return [
            'status' => true,
            'telegram_id' => UserConfig::saveTelegramID($user->id, $telegram_id) ? (int)$telegram_id : 'error!',
            'data' => Accounts::getByUser($user->id)
        ];

    }

    public function actionSetAccountStatus(){

        $wall_id = \Yii::$app->request->post('wall_id');
        $status = \Yii::$app->request->post('status');

        if(!$wall_id) {
            return [
                'status' => false,
                'error' => 'Account ID error!'
            ];
        }

        if(!isset($status) || (int)$status > 1) {
            return [
                'status' => false,
                'error' => 'Status error!',
            ];
        }
        $acc = Accounts::find()->where(['LIKE', 'data', preg_replace("/[^0-9]/", '', $wall_id)])->one();
        if(!$acc){
            return [
                'status' => false,
                'error' => 'Account not found!'
            ];
        }
        $acc = Accounts::setStatus($acc->id, $status);
        if($acc){
            return [
                'status' => true,
            ];
        }else{
            return [
                'status' => false,
                'error' => 'Save error!'
            ];
        }
    }

}