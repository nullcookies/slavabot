<?php
namespace frontend\controllers;

use common\models\User;
use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use common\commands\command\CheckStatusNotificationCommand;

use SoapClient;


class DevController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index'],
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
                //'only' => ['index'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    public function actionIndex(){

        $wsdl = 'http://sm.mlg.ru/services/CubusService.svc?wsdl';

        $trace = true;
        $exceptions = false;

       // $client = new SoapClient($wsdl, array('trace' => $trace, 'exceptions' => $exceptions));

        $xmlString = "<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:tem=\"http://tempuri.org/\" xmlns:mlg=\"http://schemas.datacontract.org/2004/07/MlgBuzz.Web.Services\">
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
                             <tem:reportId>77357</tem:reportId>
                             <!--Optional:-->
                             <tem:dateFrom>2018-03-10T19:00:00.000Z</tem:dateFrom>
                             <!--Optional:-->
                             <tem:dateTo>2018-03-14T18:59:59.999Z</tem:dateTo>
                             <!--Optional:-->
                             <tem:pageIndex>1</tem:pageIndex>
                             <!--Optional:-->
                             <tem:pageSize>100</tem:pageSize>
                          </tem:GetPosts>
                       </soapenv:Body>
                    </soapenv:Envelope>";

        $url = $wsdl;

        $header = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"http://tempuri.org/ICubusService/GetPosts\"",
            "Content-length: ".strlen($xmlString),
            "Host: sm.mlg.ru"
        );

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
        echo '<pre>';
        print_r($responseArray);
        echo '</pre>';

    }
}