<?php
namespace frontend\controllers;

use yii\web\Controller;
use VkAuth;
use linslin\yii2\curl;


class VKController extends Controller
{
    const CLIENT_ID = 6223471;
    const SECRET_KEY = 'ilsUQvhc50T6nEdsjzWS';

    public function authVK($login, $password){
        $agent = new VkAuth\VkAuthAgent($login, $password);
        $remixsid = $agent->getRemixsid();

        if($remixsid){

            $jar = $agent->getAuthorizedCookieJar()->toArray();

            $arrConnect = [
                'client_id'=> VKController::CLIENT_ID,
                'display' => 'mobile',
                'response_type'=> 'token',
                'scope'=> 'wall,photos,friends,groups',
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
                    Yii::$app->response->redirect('http://'.$_SERVER['SERVER_NAME'].'/social/vk?'.$result);
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
                return 'http://'.$_SERVER['SERVER_NAME'].'/social/vk?'.$result;
            }
        }else{
            return false;
        }
    }
}