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
use Vk;
use Facebook\Facebook;

use frontend\controllers\VKController;



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
                        'actions' => ['instagram', 'finish-process', 'update-process'],
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
                'only' => ['instagram', 'accounts', 'unprocessed', 'finish-process', 'remove', 'update-process'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * Возвращает кнопку для авторизации пользователя через вк
     */

    function getVKBtn($redirect_uri, $text='', $id){

        $v = new Vk(array(
            'client_id' => VKController::CLIENT_ID,
            'secret_key' => VKController::SECRET_KEY,
            'scope' => 'wall',
            'v' => '5.35'
        ));

        $url = $v->get_code_token("token", $redirect_uri);

        return '<a href="'.$url.'" id="'.$id.'">' . $text . '</a>';
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
            'default_graph_version' => 'v2.10',
        ]);

        $helper = $fb->getRedirectLoginHelper();

        $permissions = ['publish_actions','manage_pages','publish_pages'];

        $loginUrl = $helper->getLoginUrl($callback, $permissions);

        return '<a href="'.htmlspecialchars($loginUrl).'" id="'.$id.'">' . $text . '</a>';
    }

    public function actionFb()
    {
        // App ID и App Secret из настроек приложения
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
            // When Graph returns an error
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

// Logged in
        echo '<h3>Access Token</h3>';
        var_dump($accessToken->getValue());

// The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

// Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        echo '<h3>Metadata</h3>';
        var_dump($tokenMetadata);

        $data = $tokenMetadata;

        $tokenMetadata->validateAppId($app_id);

        $tokenMetadata->validateExpiration();

        var_dump($data);
        /* PHP SDK v5.0.0 */
        /* make the API call */
//        try {
//            // Returns a `Facebook\FacebookResponse` object
//            $response = $fb->get(
//                '/{user-id}/groups',
//                '{access-token}'
//            );
//        } catch(FacebookResponseException $e) {
//            echo 'Graph returned an error: ' . $e->getMessage();
//            exit;
//        } catch(FacebookSDKException $e) {
//            echo 'Facebook SDK returned an error: ' . $e->getMessage();
//            exit;
//        }
//        $graphNode = $response->getGraphNode();
//
//        var_dump($graphNode);
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


    public function actionVk()
    {
        // Костыль для конвертации standalone-данных vk api в get параметры

        if(!\Yii::$app->request->get('access_token')){
            echo '<script>window.location.href = document.location.href.replace("#","?");</script>';
            return false;
        }


        $config = array(
            'secret_key' => VKController::SECRET_KEY,
            'client_id' => VKController::CLIENT_ID,
            'user_id' => \Yii::$app->request->get('user_id'),
            'access_token' => \Yii::$app->request->get('access_token'),
            'scope' => 'stats, photo_100'
        );

        $v = new Vk($config);

        $response = $v->api('groups.get', array(
            'user_id' => $config['user_id'],
            'extended' => 1,
            'filter' => 'admin,editor,wall_id'
        ));

        $user = $v->api('users.get', array(
            'user_ids' => (string)$config['user_id'],
            'fields' => 'photo_50, photo_100, photo_200'
        ));

        $res = array(
            'type' => 'vkontakte',
            'data' => array(
                'user_name' => $user[0]['first_name'] . ' ' . $user[0]['last_name'],
                'user_id' => $config['user_id'],
                'access_token' => $config['access_token'],
                'groups' => $response['items']
            )
        );
        $res['data']['groups'][] = array(
            'id' => $config['user_id'],
            'name' => 'Стена пользователя ' . $res['data']['user_name'],
            'photo_50' => $user[0]['photo_50'],
            'photo_100' => $user[0]['photo_100'],
            'photo_200' => $user[0]['photo_200']
        );

        if(Accounts::saveReference($res, 0)){
            Yii::$app->response->redirect('/#/pages/social');
        }
    }
}