<?php

/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 31.01.2018
 * Time: 9:31
 */

namespace frontend\controllers;
use common\models\billing\Payment;
use YandexCheckout\Request\Payments\Payment\CreateCaptureResponse;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;


/**
 * Site controller
 */
class PaymentController extends Controller
{

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['post'],
                    'info' => ['post'],
                ],
            ],
            [
                'class' => \yii\filters\ContentNegotiator::className(),
                'only' => ['index','info'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        if (in_array($action->id, ['info'])) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        $orderId = Yii::$app->request->post('order');
        $order = Payment::findOne($orderId);
        if (!$order) {
            throw new NotFoundHttpException();
        }
        $payment = Yii::$app->yakassa->createPayment($order->getTotalPrice(), $order->getId());
        return [
            'redirectUrl' => $payment->getConfirmationUrl()
        ];
    }

    public function actionSuccess()
    {

    }

    public function actionInfo()
    {
        $request = Yii::$app->request->getRawBody();
        $info = Json::decode($request, false);
        $orderId = $info->object->metadata->orderId;
        /** @var CreateCaptureResponse $capture */
        $capture = Yii::$app->yakassa->capturePayment($info);
        if ($capture->getStatus() === 'succeeded') {
            $order = Payment::findOne($orderId);
            $order->setActivePayment();
        }
    }

}