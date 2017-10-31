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
                        'actions' => ['instagram'],
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
                'only' => ['instagram', 'accounts'],
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
            'redirect_uri'  => $redirect_uri.'?FMU='.$FMU,
            'response_type' => 'code',
            'scope'         => 'email'
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

    public function actionAccounts(){
        return Accounts::getAccounts();
    }

    public function actionVk()
    {
        $redirect_uri = 'http://'.$_SERVER['SERVER_NAME'].'/#/pages/social';
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

            $token=json_decode(file_get_contents('//oauth.vk.com/access_token' . '?' . urldecode(http_build_query($params))), true);
            if (isset($token['access_token'])) {
                $params['access_token']=$token['access_token'];

                $userInfo = json_decode(file_get_contents('//api.vk.com/method/users.get' . '?' . urldecode(http_build_query($params))), true);
                if (isset($userInfo['response'][0]['uid'])) {
                    $userInfo = $userInfo['response'][0];
                    $result = true;
                }
            }

            $file = 'social.txt';

            $current = file_get_contents($file);

            $new = json_encode(111);
            $current .= $new."\n";
            file_put_contents($file, $current);
            var_dump($token);


        }
    }


}