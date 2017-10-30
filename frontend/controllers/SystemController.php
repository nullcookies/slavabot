<?php
namespace frontend\controllers;

use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use frontend\models\UserConfig;
use common\models\User;
use common\models\Webhooks;


/**
 * Site controller
 */
class SystemController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['help', 'contact'],
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['help', 'contact'],
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
                'only' => ['help', 'contact'],
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


    public function actionHelp()
    {
        $tpmail = 'a.gorbachev@digitalwand.ru';

        $user = UserConfig::getUserData();

        $text = Yii::$app->request->post('text');

        $html = '
            <p>'.$user['name'].'</p>
            <p>'.$user['email'].'</p>
            <p>'.$user['phone'].'</p>
            <p>'.$text.'</p>
        ';

        $mail = Yii::$app->mailer
            ->compose('layouts/html', ['content' => $html])
            ->setFrom([$user['email'] => $user['name']])
            ->setTo($tpmail)
            ->setSubject('Новое сообщение в техническую поддержку: [' . $user['id'] . '] ' . $user['name'])
            ->send();

        return $mail;
    }


    public function validateUser($authKey){
        $user = User::findOne([
                'auth_key' => $authKey,
            ]);
        return \Yii::$app->user->login($user);
    }

    public function actionContact()
    {
        if(isset($_GET['code'])){
            if(self::validateUser(Yii::$app->request->get('code'))){
                $contact = Webhooks::getWebHook((int)Yii::$app->request->get('contact'));
                if(Webhooks::SetWebhookOwner(\Yii::$app->user->identity->id, $contact->id)){
                    Yii::$app->response->redirect('/#/potential/detail/'.$contact->id);
                }else{
                    Yii::$app->response->redirect('/#/error/owner');
                }
            }
        }
    }

}
