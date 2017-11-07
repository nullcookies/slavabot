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

use frontend\controllers\VKController;
use Vk;


class V1Controller extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['get-user-accounts', 'set-account-status', 'get-token'],
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['get-user-accounts', 'set-account-status', 'get-token'],
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
                'only' => ['get-user-accounts', 'set-account-status', 'get-token'],
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
        if ($action->id == 'get-user-accounts' || $action->id == 'set-account-status' || $action->id == 'get-token') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     *
     *  Отдаем аккаунты пользователя по связке логин-пароль от salesbot
     *
     *  @return array
     *
     */

    public function actionGetUserAccounts(){

        $login = \Yii::$app->request->post('login');
        $password = \Yii::$app->request->post('password');

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
            'data' => Accounts::getByUser($user->id)
        ];

    }

    public function actionSetAccountStatus(){

        $user_id = \Yii::$app->request->post('user_id');
        $account_id = \Yii::$app->request->post('account_id');
        $status = \Yii::$app->request->post('status');

        if(!$user_id){
            return [
                'status' => false,
                'error' => 'User ID error!'
            ];
        }

        if(!$account_id) {
            return [
                'status' => false,
                'error' => 'Account ID error!'
            ];
        }

        if(!$status) {
            return [
                'status' => false,
                'error' => 'Status error!'
            ];
        }

        $acc = Accounts::setStatus($user_id, $account_id, $status);

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