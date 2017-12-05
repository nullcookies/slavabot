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
use common\models\Accounts;
use common\models\Instagram;
use Vk;
use Facebook\Facebook;
use VkAuth;
use frontend\controllers\VKController;
use linslin\yii2\curl;
use common\services\social\FacebookService;

class SocialController extends Controller
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
                        'actions' => ['instagram', 'finish-process', 'update-process', 'vk-auth'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'vk-auth' => ['post'],
                ],
            ],
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                'only' => [
                    'instagram',
                    'accounts',
                    'unprocessed',
                    'finish-process',
                    'remove',
                    'update-process',
                    'vk-auth',
                    'check-instagram'
                    ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }


    function getFBBtn($callback, $text='', $id, $template='<a href="LINK" id="ID">TEXT</a>'){

        $fb = new FacebookService;

        return self::useTemplate(
            $template,
            [
                '/LINK/',
                '/ID/',
                '/TEXT/'
            ],
            [
                htmlspecialchars($fb->link($callback)),
                $id,
                $text
            ]
        );
    }

    function useTemplate($string, $patterns, $replacements){
        return preg_replace($patterns, $replacements, $string);
    }

    public function actionFb()
    {
        $fb = new FacebookService;

        if(Accounts::saveReference($fb->process(), 0)){
            Yii::$app->response->redirect('/#/pages/social');
        }
    }

    public function actionWizardFb()
    {
        $this->layout = '@app/views/layouts/simple.php';

        $fb = new FacebookService;

        if(Accounts::saveReference($fb->process(), 0)){
            return '<script>window.close();</script>';
        }
    }

    public function actionIndex()
    {

        $this->layout = '@app/views/layouts/simple.php';


        return $this->render('social', [
            'accounts'=> Accounts::getAccounts()
        ]);

    }

    public function actionRemove()
    {
        $id = \Yii::$app->request->post('id');
        return Accounts::remove($id);
    }

    public function actionCheckInstagram()
    {
        return Instagram::login();
    }

    public function actionInstagram(){

        return Accounts::saveReference(\Yii::$app->request->post());
    }

    public function actionFinishProcess(){
        return Accounts::processAccount(\Yii::$app->request->post());
    }

    public function actionUpdateProcess(){
        return Accounts::updateAccount(\Yii::$app->request->post());
    }

    public function actionAccounts(){
        return Accounts::getAccounts();
    }

    public function actionUnprocessed($type = ''){
        if(\Yii::$app->request->post()['type']){
            $type = \Yii::$app->request->post()['type'];
        }
        return Accounts::getUnprocessedAccounts($type);
    }


    function actionVkAuth()
    {
        $login = Yii::$app->request->post('login');
        $password = Yii::$app->request->post('password');

        try {
            $response = VKController::authVK($login, $password);
        } catch (\Exception $ex) {

            return [
                'status' => false,
                'error' => explode(' => ', $ex->getMessage())[1]
                ];
        }

            parse_str($response, $params);

            ob_start();

            $res = VKController::initVKApi($params, $login, $password);

            $save = Accounts::saveReference($res,  0);

            ob_end_clean();
            if($save){
                return [
                    'status' => true,
                ];
            }else{
                return [
                    'status' => false,
                    'error' => 'Get token error'
                ];
            }

    }
}