<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 10.11.2017
 * Time: 11:46
 */

namespace frontend\controllers\bot\libs;


class SocialNetworks
{
    //названия соц.сетей соответсвуют ключам type из /rest/accounts/v1/get-user-accounts/
    const VK = 'vkontakte';
    const FB = 'facebook';
    const IG = 'instagram';


    /**
     * Возвращает массив параметров для подключения в соц сети
     * формат:
     *  vk:
     *   access_token: e1c0e248c03c6e709e369626f2e8957519f1f36022754b2ba5dd58c43d42f75befddee4c5b39762c91b78
     *   wall_id: 155979896
     *   page_access_token: e795319c9837e69b586075a8106f53af504a466c5cb93c15011be9d56ddc3bb998698cba57159b7b87d3a
     *  fb:
     *   page_id: 2016066731963875
     *   page_access_token: EAAaTiDoBLssBAAshuEJTAjPIuo2q2h12y3X0n71x471ustF9tiZCZBbC1MXS6ZATzRs6wT5whHqARKoNJJO4d1PFYup9ZBoaZBSxLiXhdvKJoOLTgXY9O7WHItQrSEWUArxcTmhsgsqJ2mnbzqya4k32dMD3KHuxRZBqC2b6Hbu2EZAtH3qZBfNvIjkTQFJCX9IZD
     *   access_token:
     *  ig:
     *   username:
     *   password:
     *
     * @param $arRequest - ответ от апи
     * @param $network_key - соцсеть. названия тут \libs\SocialNetworks.php
     *
     * @return array
     */
    public static function getParams($arRequest, $network_key){

        $arResult = array();

        foreach ($arRequest['data'] as $data ) {

            if (($network_key == self::VK) && ($data['type'] == self::VK)) {
                $arResult['access_token'] = $data['data']['access_token'];

                //todo обработать вывод записи на стене группы
                // из дока - https://vk.com/dev/wall.post
                /**
                * Обратите внимание, идентификатор сообщества в параметре owner_id необходимо указывать со знаком "-" — например, owner_id=-1 соответствует идентификатору сообщества ВКонтакте API (club1)
                */
                //с минусом
                //$owner = '-'.$notes['wall_id'];
                if ( $data['data']['user_id'] == $data['data']['groups']['id']) {
                    $arResult['wall_id'] = $data['data']['user_id'];
                } else {
                    $arResult['wall_id'] = '-'.$data['data']['groups']['id'];
                }

                //хз откуда этот параметр. но был в конфиге, пока пустой
                $arResult['page_access_token'] = '';

                //TODO добавить отработку групп [data][groups]

            }

            if (($network_key == self::FB) && ($data['type'] == self::FB)) {
                $arResult['page_id'] = $data['data']['groups']['id'];
                //$arResult['access_token'] = $data['data']['access_token'];
                $arResult['page_access_token'] = $data['data']['access_token'];

                //для публикации в группу нужен доп. запрос токена.
                //добавляем флаг
                if ($data['data']['groups']['access_token']) {
                    $arResult['is_group'] = TRUE;
                }

            }

            if (($network_key == self::IG) && ($data['type'] == self::IG)) {
                $arResult['username'] = $data['data']['login'];
                $arResult['password'] = $data['data']['password'];
            }

        }

        return $arResult;
    }

}