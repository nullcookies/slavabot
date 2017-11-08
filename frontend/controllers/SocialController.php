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