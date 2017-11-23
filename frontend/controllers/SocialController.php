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


    /**
     * Возвращает кнопку для авторизации пользователя через вк
     */

    function getFBBtn($callback, $text='', $id){
        session_start();

        $app_id = "169360780313874";
        $app_secret = "38e43b5ab78044815bcc51314fdb20a0";


        $fb = new Facebook([
            'app_id'  => $app_id,
            'app_secret' => $app_secret,
            'default_graph_version' => 'v2.11',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        $permissions = ['public_profile', 'publish_actions','manage_pages','publish_pages', 'pages_show_list'];

        $loginUrl = $helper->getLoginUrl($callback, $permissions);

        return '<a href="'.htmlspecialchars($loginUrl).'" id="'.$id.'">' . $text . '</a>';
    }

    public function actionFb()
    {
        $app_id = "169360780313874";
        $app_secret = "38e43b5ab78044815bcc51314fdb20a0";

        $fb = new Facebook([
            'app_id'  => $app_id,
            'app_secret' => $app_secret,
            'default_graph_version' => 'v2.10',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch(FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        if (! isset($accessToken)) {
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: " . $helper->getError() . "\n";
                echo "Error Code: " . $helper->getErrorCode() . "\n";
                echo "Error Reason: " . $helper->getErrorReason() . "\n";
                echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }


        $oAuth2Client = $fb->getOAuth2Client();

        $tokenMetadata = $oAuth2Client->debugToken($accessToken);

        $tokenMetadata->validateAppId($app_id);

        $tokenMetadata->validateExpiration();

        try {
            $response = $fb->get('/me?fields=id,name', "{$accessToken}");
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $user = $response->getGraphNode();

        try {
            $response = $fb->get('/me/groups', "{$accessToken}");
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $groups = $response->getGraphEdge()->asArray();
        foreach($groups as $gr){
                 try {
                     $response = $fb->get('/'.$gr['id'].'?fields=id,name', "{$accessToken}");
                 } catch(Facebook\Exceptions\FacebookResponseException $e) {
                     echo 'Graph returned an error: ' . $e->getMessage();
                     exit;
                 } catch(Facebook\Exceptions\FacebookSDKException $e) {
                     echo 'Facebook SDK returned an error: ' . $e->getMessage();
                     exit;
                 }
        }

        try {
            $longToken = $fb->get('/oauth/access_token?grant_type=fb_exchange_token&client_id='.$app_id.'&client_secret='.$app_secret.'&fb_exchange_token='.$accessToken->getValue('value'), $accessToken);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        $long_token = $longToken->getGraphNode()->asArray();
        $date = new \DateTime();
        $long_token['start'] = $date->getTimestamp();

        $res = array(
            'type' => 'facebook',
            'data' => array(
                'user_name' => $user['name'],
                'user_id' => $user['id'],
                'access_token' => $long_token['access_token'],
                'groups' => $groups
            )
        );
        $res['data']['groups'][] = array(
            'id' => $user['id'],
            'name' => 'Стена пользователя ' . $user['name'],
        );

//        try {
//            $response = $fb->post(
//                '/'.$user['id'].'/feed',
//                array (
//                    'message' => 'This is a test message',
//                ),
//                $long_token['access_token']
//            );
//        } catch(Facebook\Exceptions\FacebookResponseException $e) {
//            echo 'Graph returned an error: ' . $e->getMessage();
//            exit;
//        } catch(Facebook\Exceptions\FacebookSDKException $e) {
//            echo 'Facebook SDK returned an error: ' . $e->getMessage();
//            exit;
//        }
//        $graphNode = $response->getGraphNode();


        if(Accounts::saveReference($res, 0)){
            Yii::$app->response->redirect('/#/pages/social');
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
        return Instagram::login('vedensky_lx', 'zdx1991L');
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

    public function actionUnprocessed(){
        return Accounts::getUnprocessedAccounts();
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

    public function actionVkTest(){

        $config = array(
            'secret_key' => VKController::SECRET_KEY,
            'client_id' => VKController::CLIENT_ID,
            'user_id' => 12620990,
            'access_token' => '3db2a46b71261ac2edd70a314e4f590a86f62dae684e67ffd0cf08880011ab47ba472857e0a09f1a539a5',
            'scope' => 'stats, photo_100,wall,groups,photos,video'
        );

        $v = new Vk($config);
    }
}