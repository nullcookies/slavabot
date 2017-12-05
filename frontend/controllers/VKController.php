<?php
namespace frontend\controllers;

use yii\web\Controller;
use VkAuth;
use Vk;
use linslin\yii2\curl;


class VKController extends Controller
{
    const CLIENT_ID = 6223471;
    const SECRET_KEY = 'ilsUQvhc50T6nEdsjzWS';

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
                'only' => ['instagram', 'accounts', 'unprocessed', 'finish-process', 'remove', 'update-process', 'auth-vk'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }



    public function authVK($login, $password){
        $agent = new VkAuth\VkAuthAgent($login, $password);
        $remixsid = $agent->getRemixsid();

        if($remixsid){

            $jar = $agent->getAuthorizedCookieJar()->toArray();

            $arrConnect = [
                'client_id'=> VKController::CLIENT_ID,
                'display' => 'mobile',
                'response_type'=> 'token',
                'scope'=> 'offline,wall,photos,friends,groups,messages,notifications',
                'v'=> '5.28'
            ];

            $cook = '';

            foreach($jar as $i => $elem){
                $cook.= $elem['Name'].'='.$elem['Value'].'; ';
            }

            $curl = new curl\Curl();

            $response = $curl
                ->setPostParams($arrConnect)
                ->setHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json',
                    'cookie' => $cook
                ])
                ->post('https://oauth.vk.com/authorize');

            if(preg_match('/<form.*<\/form>/sU', $response, $matches)){
                $iframe = array_shift($matches);
                if(preg_match('/action=["\'].*["\']/U', $iframe, $width)){
                    $widthValue = preg_replace('/(action=["\'])(.*)(["\'])/U', '${2}', $width);
                    $width = array_shift($width);
                    $widthValue = array_shift($widthValue);
                    $response1 = $curl->setOption(CURLOPT_HEADER, true)->post($widthValue);
                    $url = $curl->responseHeaders['Location'];

                    $result = substr(strstr($url, '#'), 1, strlen($url));
                    return $result;
                }
            }else{
                $response = $curl
                    ->setPostParams($arrConnect)
                    ->setOption(CURLOPT_HEADER, true)
                    ->setHeaders([
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept' => 'application/json',
                        'cookie' => $cook
                    ])
                    ->post('https://oauth.vk.com/authorize');

                $response1 = $curl->setOption(CURLOPT_HEADER, true)->post($curl->responseHeaders['Location']);

                $url = $curl->responseHeaders['Location'];
                $result = substr(strstr($url, '#'), 1, strlen($url));
                return $result;
            }
        }else{
            return false;
        }
    }

    public function initVKApi($params,  $login, $password){

        $config = array(
            'secret_key' => VKController::SECRET_KEY,
            'client_id' => VKController::CLIENT_ID,
            'user_id' => $params['user_id'],
            'access_token' => $params['access_token'],
            'scope' => 'offline, stats, photo_100,wall,groups,photos,video'
        );

        $v = new Vk($config);

        $groups = $v->api('groups.get', array(
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
                'login' => $login,
                'password' => $password,
                'user_name' => $user[0]['first_name'] . ' ' . $user[0]['last_name'],
                'user_id' => $config['user_id'],
                'access_token' => $config['access_token'],
                'expires_in' => $params['expires_in'],
                'groups' => $groups['items']
            )
        );

        $res['data']['groups'][] = array(
            'id' => $config['user_id'],
            'name' => 'Стена пользователя ' . $res['data']['user_name'],
            'photo_50' => $user[0]['photo_50'],
            'photo_100' => $user[0]['photo_100'],
            'photo_200' => $user[0]['photo_200']
        );


        return $res;
    }
}