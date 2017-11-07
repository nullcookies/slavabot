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
                        'actions' => ['instagram', 'finish-process'],
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
                'only' => ['instagram', 'accounts', 'unprocessed', 'finish-process'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    const CLIENT_ID ='6234561'; // ID приложения VK
    const CLIENT_SECRET = 'gXvqte2SHQw6oGjGpKTM'; // Ключ приложения

    /**
     * Возвращает кнопку для авторизации пользователя через вк
     */

    function getVKBtn($redirect_uri, $text='', $FMU=''){

        $url='//oauth.vk.com/authorize';

        $params = array(
            'client_id'     => self::CLIENT_ID,
            'redirect_uri'  => $redirect_uri,
            'response_type' => 'code',
            'scope'         => 'stats'
        );

        return '<a href="' . $url . '?' . urldecode(http_build_query($params)) . '">' . $text . '</a>';
    }

    public function actionIndex()
    {

        $this->layout = '@app/views/layouts/simple.php';


        return $this->render('social', [
            'accounts'=> Accounts::getAccounts()
        ]);

    }

    public function actionInstagram(){

        return Accounts::saveReference(\Yii::$app->request->post());
    }

    public function actionFinishProcess(){
        return Accounts::processAccount(\Yii::$app->request->post());
    }

    public function actionAccounts(){
        return Accounts::getAccounts();
    }

    public function actionUnprocessed(){
        return Accounts::getUnprocessedAccounts();
    }


    public function actionVk()
    {
        $redirect_uri = 'http://'.$_SERVER['SERVER_NAME'].'/social/vk';
        $code = $_GET['code'];
        $fmu = $_GET['FMU'];

        if (isset($code)) {
            $result = false;

            $params = array(
                'client_id' => self::CLIENT_ID,
                'client_secret' => self::CLIENT_SECRET,
                'code' => $code,
                'redirect_uri' => $redirect_uri,
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://oauth.vk.com/access_token' . '?' . urldecode(http_build_query($params, '', '&', PHP_QUERY_RFC3986 )));
            curl_setopt($ch,CURLOPT_USERAGENT,"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($result);

            $config['secret_key'] = 'ilsUQvhc50T6nEdsjzWS';
            $config['client_id'] = 6223471; // номер приложения
            $config['user_id'] = $result->user_id; // id текущего пользователя (не обязательно)
            $config['access_token'] = $result->access_token;
            $config['scope'] = 'stats'; // права доступа к методам (для генерации токена)

            $v = new Vk($config);

            $response = $v->api('groups.get', array(
                'user_id' => $config['user_id'],
                'extended' => 1,
                'filter' => 'admin,editor'
            ));

            $user = $v->api('users.get', array(
                'user_ids' => (string)$config['user_id']
            ));

            $res = array(
                'type' => 'vkontakte',
                'data' => array(
                    'user_name' => $user[0]['first_name'] . ' ' . $user[0]['last_name'],
                    'user_id' => $result->user_id,
                    'access_token' => $result->access_token,
                    'groups' => $response['items']
                )
            );

            if(Accounts::saveReference($res, 0)){
                Yii::$app->response->redirect('/#/pages/social');
            }
        }
    }
}