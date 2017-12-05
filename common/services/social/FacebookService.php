<?php
/**
 * Class FacebookService
 * Работа с Fb
 * @package common\services\social
 */

namespace common\services\social;
use \Facebook\Facebook as FB;
use common\services\StaticConfig;


class FacebookService
{
    protected $app_id;
    protected $app_secret;
    protected $version;

    /**
     * FacebookService constructor.
     * Подгружаем настройки приложения fb
     */
    public function __construct()
    {
        $this->app_id = StaticConfig::facebook()['app_id'];
        $this->app_secret = StaticConfig::facebook()['app_secret'];
        $this->version = StaticConfig::facebook()['version'];
    }

    /**
     * @return FB
     */
    public function init(){
        return new FB([
            'app_id'  => $this->app_id,
            'app_secret' => $this->app_secret,
            'default_graph_version' => $this->version,
        ]);
    }

    /**
     * Получаем необходимые данные от апи
     *
     * @return array
     */
    public function process(){

        $fb = self::init();

        $accessToken = self::accessToken($fb->getRedirectLoginHelper());

        $oAuth2Client = $fb->getOAuth2Client();
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        $tokenMetadata->validateAppId($this->app_id);
        $tokenMetadata->validateExpiration();

        $user = self::getUser($fb, $accessToken);
        $groups = self::getGroups($fb, $accessToken);
        $accounts = self::getAccounts($fb, $accessToken);

        $long_token = self::getLongToken($fb, $accessToken);

        $res = array(
            'type' => 'facebook',
            'data' => array(
                'user_name' => $user['name'],
                'user_id' => $user['id'],
                'access_token' => $long_token['access_token'],
                'groups' => array_merge($accounts, $groups),
            )
        );

        $res['data']['groups'][] = array(
            'id' => $user['id'],
            'name' => 'Стена пользователя ' . $user['name'],
        );

        return $res;
    }

    /**
     * Генерируем ссылку для авторизации через fb
     *
     * @param $callback
     * @return string
     */
    public function link($callback){
        session_start();

        $fb = self::init();

        $helper = $fb->getRedirectLoginHelper();

        $permissions = StaticConfig::facebook()['permissions'];

        return $helper->getLoginUrl($callback, $permissions);
    }

    /**
     * Получаем кратковременный access_token
     *
     * @param $helper
     * @return mixed
     */
    public function accessToken($helper){
        try {
            $accessToken = $helper->getAccessToken();
            if (! isset($accessToken)) {
                if ($helper->getError()) {
                    header('HTTP/1.0 401 Unauthorized');
                    echo "Error: " . $helper->getError() . "\n";
                    echo "Error Code: " . $helper->getErrorCode() . "\n";
                    echo "Error Reason: " . $helper->getErrorReason() . "\n";
                    echo "Error Description: " . $helper->getErrorDescription() . "\n";
                    exit;
                } else {
                    header('HTTP/1.0 400 Bad Request');
                    echo 'Bad request';
                    exit;
                }
                exit;
            }else{
                return $accessToken;
            }
        } catch(FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

    /**
     * Получаем данные о пользователе
     * @param $fb
     * @param $accessToken
     * @return mixed
     */
    public function getUser($fb, $accessToken){
        try {
            $response = $fb->get('/me?fields=id,name', "{$accessToken}");
            return $response->getGraphNode();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

    /**
     * Получаем данные о группах пользователя
     * @param $fb
     * @param $accessToken
     * @return mixed
     */
    public function getGroups($fb, $accessToken){
        try {
            $response = $fb->get('/me/groups', "{$accessToken}");
            return $response->getGraphEdge()->asArray();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

    /**
     * Получаем данные о страницах пренадлежащих пользователю
     * Это как группы, только не группы =)
     *
     * У страниц есть свой access_token, причем он краткосрочный.
     *
     * @param $fb
     * @param $accessToken
     * @return mixed
     */
    public function getAccounts($fb, $accessToken){
        try {
            $responseAccounts = $fb->get('/me/accounts', "{$accessToken}");
            return $responseAccounts->getGraphEdge()->asArray();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

    /**
     * Получение долгосрочного токена
     * @param $fb
     * @param $accessToken
     * @return mixed
     */
    public function getLongToken($fb, $accessToken){
        try {
            $longToken = $fb->get('/oauth/access_token?grant_type=fb_exchange_token&client_id='.$this->app_id.'&client_secret='.$this->app_secret.'&fb_exchange_token='.$accessToken->getValue('value'), $accessToken);
            return $longToken->getGraphNode()->asArray();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }
}