<?php
/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 12.01.2018
 * Time: 09:16
 */

namespace common\commands\command;
use Carbon\Carbon;
use common\models\Reports;
use trntv\bus\interfaces\SelfHandlingCommand;
use yii\base\Object as BaseObject;


class GetPostsCommand extends BaseObject implements SelfHandlingCommand
{

    public $period;
    public $wsdl;

    public function handle($command)
    {

        $response = $this->getReports(
            Reports::getActiveIDs(),
            $command->period,
            $command->wsdl
        );

        return $response;
    }

    public function getReports($ids, $period, $wsdl){

        $res = array();

        foreach($ids as $id){

            $response = $this->getReport($id, $period, $wsdl);

            if(is_array($response)){
                foreach($response as $post){
                    $res[] = $this->convertPost($post, $id);
                }
            }
        }

        return $res;
    }

    public function getReport($id, $period, $wsdl){

        $finish = Carbon::now();

        $start = Carbon::now()->subMinutes($period);

        $xmlString = $this->makeXML($id, $this->timeConvert($start), $this->timeConvert($finish));

        $header = $this->makeHeaders($xmlString);

        $response = $this->sendRequest($wsdl,$xmlString, $header);

        return $response;
    }

    public function timeConvert($time){
        return $time->format('Y-m-d').'T'.$time->format('H:i:s').'.002Z';
    }

    public function makeXML($reposrt_id, $start, $finish){
         return "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:tem=\"http://tempuri.org/\" xmlns:mlg=\"http://schemas.datacontract.org/2004/07/MlgBuzz.Web.Services\">
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
    }

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

    public function convertPost ($post, $theme){

        return [
            'id' => $post['aPostId'],
            'number' => '',
            'client' => '',

            'location' => '',
            'category' => '',
            'priority' => '',
            'theme' => $theme,

            'post_url' => $post['aUrl'],
            'author_image_url' => $post['aAuthorImageUrl'],
            'author_url' => $post['aAuthorUrl'],
            'post_content' => $post['aContent'],
            'author_name' => $post['aAuthorName'],
            'social' => '',
            'created_at' => ''
        ];

    }
}