<?php
/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 12.01.2018
 * Time: 09:16
 *
 * Команда для получения постов из sm.mlg.ru
 *
 */

namespace common\commands\command;
use Carbon\Carbon;
use common\models\ABlog;
use common\models\ACity;
use common\models\ACountry;
use common\models\ARegion;
use common\models\Reports;
use common\models\Webhooks;
use frontend\controllers\bot\libs\Logger;
use trntv\bus\interfaces\SelfHandlingCommand;
use yii\base\Object as BaseObject;


class GetPostsCommand extends BaseObject implements SelfHandlingCommand
{

    /**
     * Параметры:
     *
     * $period - интервал в минутах, за который мы получим посты по интересующим фильтрам
     * $wsdl - адрес api
     *
     */

    public $period;
    public $wsdl;

    /**
     * Обработчик комманды
     *
     * @param $command
     * @return array
     */
    public function handle($command)
    {

        $response = $this->getReports(
            Reports::getActiveIDs(),
            $command->period,
            $command->wsdl
        );

//        Logger::report('Start:', [
//            'Period' => $command->period
//        ]);

        return $response;
    }

    /**
     * Получить данные по всем отчетам
     *
     * @param $ids - идентификаторы отчетов, которые нужно загрузить
     * @param $period - интервал
     * @param $wsdl - адрес api
     * @return array - отформатированный под нашу бд массив данных
     */

    public function getReports($ids, $period, $wsdl){

        $res = array();

        foreach($ids as $id){

            $response = $this->getReport($id, $period, $wsdl);
            Logger::report('Data:', [
                'theme' => $id,
                'response' => $response
            ]);

            if(isset($response['aPostId'])){
                $data = $this->convertPost($response, $id);

                if(is_array($data)){

                    $res[] = Webhooks::savePost($data);
                }

            }else if(is_array($response) && !isset($response['aPostId'])){
                foreach($response as $post){
                    $data = $this->convertPost($post, $id);

                    if(is_array($data)){

                        $res[] = Webhooks::savePost($data);
                    }
                }
            }
        }

        return $res;
    }

    /**
     * Получаем отчет по конкретному id
     *
     * @param $id - идентификатор отчета
     * @param $period - интервал
     * @param $wsdl - адрес api
     * @return mixed
     */

    public function getReport($id, $period, $wsdl){

        $finish = Carbon::now()->setTimezone('UTC')->subMinutes(80);

        $start = Carbon::now()->setTimezone('UTC')->subMinutes(80 + $period);

        $xmlString = $this->makeXML($id, $this->timeConvert($start), $this->timeConvert($finish));

        $header = $this->makeHeaders($xmlString);

        $response = $this->sendRequest($wsdl,$xmlString, $header);
        Logger::report('Start:', [
            'Start' => $this->timeConvert($start),
            'Finish' => $this->timeConvert($finish),
            'Report' => $id,
            'CarbonNow' => Carbon::now()->setTimezone('Europe/Moscow')
        ]);
        /**
         * Раскомментить для отладки. Отразит исходные данные (или ошибку)
         */

//        echo '<pre>';
//        print_r($response);
//        echo '</pre>';

        return $response;

    }

    /**
     * Конвертер времени под формат API
     *
     * @param $time - объект Carbon
     * @return string
     */
    public function timeConvert($time){
        return $time->format('Y-m-d').'T'.$time->format('H:i:s').'.000Z';
    }

    /**
     * XML обертка для запроса
     *
     * @param $reposrt_id - id отчета
     * @param $start - время начала периода выборки
     * @param $finish - время конца периода выборки
     * @return string
     */
    public function makeXML($reposrt_id, $start, $finish){
        $xml = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:tem=\"http://tempuri.org/\" xmlns:mlg=\"http://schemas.datacontract.org/2004/07/MlgBuzz.Web.Services\">
                       <soapenv:Header/>
                       <soapenv:Body>
                          <tem:GetPosts>
                             <!--Optional:-->
                             <tem:credentials>
                                <!--Optional:-->
                                <mlg:Login>leads.im</mlg:Login>
                                <!--Optional:-->
                                <mlg:Password>дуфвы1423</mlg:Password>
                             </tem:credentials>
                             <!--Optional:-->
                             <tem:reportId>".$reposrt_id."</tem:reportId>
                             <!--Optional:-->
                             <tem:dateFrom>".$start."</tem:dateFrom>
                             <!--Optional:-->
                             <tem:dateTo>".$finish."</tem:dateTo>
                             <!--Optional:-->
                             <tem:pageIndex>1</tem:pageIndex>
                             <!--Optional:-->
                             <tem:pageSize>100</tem:pageSize>
                          </tem:GetPosts>
                       </soapenv:Body>
                    </soapenv:Envelope>";

//                Logger::report('Try to get data:', [
//            'xml' => $xml
//        ]);
         return $xml;
    }

    /**
     * Заголовки запроса
     *
     * @param $xmlString - XML для запроса
     * @return array
     */
    public function makeHeaders($xmlString){
        return array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"http://tempuri.org/ICubusService/GetPosts\"",
            "Content-length: ".strlen($xmlString),
            "Host: sm.mlg.ru"
        );
    }

    /**
     * Запрос для получения данных
     *
     * @param $url - адрес api
     * @param $xmlString - XML для запроса
     * @param $header - заголовки запроса
     * @return mixed
     */

    public function sendRequest($url, $xmlString, $header){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlString); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $response = curl_exec($ch);
        curl_close($ch);

        $xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $responseArray = json_decode($json,true);

        return $responseArray['sBody']['GetPostsResponse']['GetPostsResult']['aPosts']['aCubusPost'];
    }

    /**
     * Конвертер данных из пришедших в наш формат
     *
     * @param $post - массив данных поста из api
     * @param $theme - id отчета из нашей бд
     * @return array
     */

    public function convertPost ($post, $theme){
        if(isset($post['aPostId'])){
            try{
                $aCountry = ACountry::getCountry(
                    $post['aCountry']
                );

                $aRegion = ARegion::getRegion(
                    $post['aRegion'],
                    $aCountry
                );

                $aCity = ACity::getCity(
                    $post['aCity'],
                    $aCountry,
                    $aRegion
                );

                return [
                    'post_id' => $post['aPostId'],

                    'category' => (int)$theme,

                    'aCity' => $aCity,
                    'aCountry' => $aCountry,
                    'aRegion' => $aRegion,

                    'post_url' => $post['aUrl'],
                    'author_image_url' => $post['aAuthorImageUrl'],
                    'author_url' => $post['aAuthorUrl'],
                    'post_content' => $post['aContent'],
                    'author_name' => $post['aAuthorName'],

                    'blog' => ABlog::getBlog(
                        $post['aBlogHost'],
                        $post['aBlogHostId'],
                        $post['aBlogHostType']
                    ),

                    'type'=> (int)$post['aMessageType'],

                    'published_at' => $this->setTime(
                        $post['aPublishDate']
                    )
                ];
            }catch (\Exception $e) {
                Logger::report('Error:', [
                    $post['aPostId'] => $e->getMessage()
                ]);
                return false;
            }



        }else{
            Logger::report('Error:', [
                'data' => $post
            ]);
            return false;
        }

    }

    /**
     * Преобразование времени sm.mlg.ru в timestamp
     * Устанавливаем пояс 'Europe/London' для удобства работы в дальнейшем.
     */

    public function setTime($time){

        $time = Carbon::parse($time)
            ->setTimezone('UTC')
            ->getTimestamp();

        return $time;
    }
}