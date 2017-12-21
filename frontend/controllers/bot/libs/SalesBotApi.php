<?php
/**
 * Класс для получения данных от http://salesbot.medialogic.ddemo.ru/api.html#
 * Created by PhpStorm.
 * User: Admin
 * Date: 08.11.2017
 * Time: 14:19
 */

namespace frontend\controllers\bot\libs;

use common\services\StaticConfig;
use GuzzleHttp\Exception\RequestException;


/**
 * Class SalesBotApi
 * @package Libs
 */
class SalesBotApi
{

    private $SalesBot;

    //основной адрес для запросов к api
    private $base_uri;

    /**
     * Создаем клиента, .
     */
    public function __construct()
    {
        $common = StaticConfig::configBot('common');

        $this->base_uri = $common['sales_bot_api'];

        $this->SalesBot = new \GuzzleHttp\Client([
            'base_uri' => $this->base_uri,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
        ]);
    }


    /**
     * Список аккаунтов пользователя
     * @param $arParams [tig]
     *
     * @return  array|bool|mixed
     * false - если не найден пользователь
     */
    public function getUserAccounts($arParams)
    {

        $arResult = array();

        try {

            $response = $this->SalesBot->request(
                'POST',
                '/rest/accounts/v1/get-user-accounts/',
                [
                    'form_params' => [
                        'tid' => $arParams['tid'],
                    ]
                ]

            );

            $arResult = json_decode($response->getBody(), true);

            if (!$arResult['status']) {
                return false;
            } else {
                return $arResult;
            }


        } catch (RequestException $e) {
            Logger::error($e->getMessage());
        }

    }

    /**
     * Изменение статуса аккаунта пользователя
     * @param $arParams [user_id,account_id,$status]
     *
     * в качестве user_id ставим  [data][0][id] из getUserAccounts
     * account_id - [data][0][user_id] из getUserAccounts
     * status может быть либо 1 либо null (пока 0 вызывает ошибку)
     *
     * @return bool
     */
    public function setUserAccountStatus($arParams)
    {

        $arResult = array();

        try {

            $response = $this->SalesBot->request(
                'POST',
                '/rest/accounts/v1/set-account-status/',
                [
                    'form_params' => [
                        'wall_id' => $arParams['wall_id'],
                        //'account_id' => $arParams['account_id'],
                        'status' => $arParams['status']
                    ]
                ]

            );


            $arResult = json_decode($response->getBody(), true);

            if ($arResult['status']) {
                return true;
            } else {

                //echo $response->getBody();

                return false;
            }

        } catch (RequestException $e) {
            Logger::error($e->getMessage());
        }
    }

    /**
     * Запрос на отправку проверочного кода пользователю по email(=login)
     * @param $arParams [login]
     *
     * @return array|bool|mixed
     */
    public function sendPassword($arParams)
    {

        $arResult = array();

        try {

            $response = $this->SalesBot->request(
                'POST',
                '/rest/user/v1/send-password/',
                [
                    'form_params' => [
                        'login' => $arParams['login'],
                    ]
                ]

            );

            $arResult = json_decode($response->getBody(), true);

            if (!$arResult['status']) {
                return false;
            } else {
                return true;
            }

        } catch (RequestException $e) {
            Logger::error($e->getMessage());
        }

    }


    /**
     * Отправляет код авторизации на проверку
     * @param $arParams [login,code,tid]
     * login - почта пользователя
     * code - код из письма
     * tid - id пользователя в telegram
     *
     * @return bool
     */
    public function authTelegram($arParams)
    {

        $arResult = array();
        Logger::info('Авторизация поьзователя', [
            'params' => $arParams
        ]);
        try {

            $response = $this->SalesBot->request(
                'POST',
                '/rest/user/v1/auth-telegram/',
                [
                    'form_params' => [
                        'login' => $arParams['login'],
                        'code' => $arParams['code'],
                        'tid' => $arParams['tid'],
                    ]
                ]

            );

            $arResult = json_decode($response->getBody(), true);

            if (!$arResult['status']) {
                return false;
            } else {
                return true;
            }

            Logger::info('Привязка поьзователя', [
                'result' => $arResult
            ]);

        } catch (RequestException $e) {
            Logger::error($e->getMessage());
        }

    }

    /**
     * отправлем данные в ЛК
     * @param $arParams
     *
     * @return bool
     */
    public function newEvent($arParams)
    {

        Logger::info('Отправлем данные в ЛК', [
            'method' => __METHOD__,
            'arParams' => $arParams
        ]);

        try {

            $response = $this->SalesBot->request(
                'POST',
                '/rest/history/v1/new-event/',
                [
                    'form_params' => [
                        'data' => $arParams['data'],
                        'type' => $arParams['type'],
                        'tid' => $arParams['tid'],
                    ]
                ]

            );

            Logger::info(__METHOD__, [
                'form_params' => [
                    'data' => $arParams['data'],
                    'type' => $arParams['type'],
                    'tid' => $arParams['tid'],
                ]
            ]);

            $arResult = json_decode($response->getBody(), true);

            if (!$arResult['status']) {
                return false;
            } else {
                return true;
            }

        } catch (RequestException $e) {
            Logger::error($e->getMessage());
        }

    }


    /**
     * Устанавливает часовой пояс пользователя
     * @param $arParams
     *
     * @return bool
     */
    public function setTimezone($arParams)
    {

        $arResult = array();

        try {

            $response = $this->SalesBot->request(
                'POST',
                '/rest/user/v1/set-time-zone/',
                [
                    'form_params' => [
                        'tid' => $arParams['tid'],
                        'timezone' => $arParams['timezone'],
                    ]
                ]

            );

            $arResult = json_decode($response->getBody(), true);

            if (!$arResult['status']) {
                return false;
            } else {
                return true;
            }

        } catch (RequestException $e) {
            Logger::error($e->getMessage());
        }

    }


    /**
     * Получаем часовой пояс из ЛК
     * @param $arParams
     *
     * @return array|bool|mixed
     */
    public function getTimezone($arParams)
    {

        $arResult = array();

        try {

            $response = $this->SalesBot->request(
                'POST',
                '/rest/user/v1/get-time-zone/',
                [
                    'form_params' => [
                        'tid' => $arParams['tid'],
                    ]
                ]

            );

            $arResult = json_decode($response->getBody(), true);

            if (!$arResult['status']) {
                return false;
            } else {
                return $arResult['timezone'];
            }

        } catch (RequestException $e) {
            Logger::error($e->getMessage());
        }

    }


    /**
     * Получаем список пользователей у которых привязана страница ВК
     * @return array
     */
    public function getVkAccounts()
    {
        try {
            $response = $this->SalesBot->request(
                'GET',
                '/rest/accounts/v1/get-vk-accounts/'
            );

            $arResult = json_decode($response->getBody(), true);

            if ($arResult['status']) {
                return $arResult['accounts'];
            } else {
                return [];
            }
        } catch (RequestException $e) {
            Logger::error($e->getMessage());
        }
    }

}