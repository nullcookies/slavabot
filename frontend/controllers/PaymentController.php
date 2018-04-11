<?php

/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 31.01.2018
 * Time: 9:31
 */

namespace frontend\controllers;

use frontend\controllers\bot\libs\Logger;
use kroshilin\yakassa\widgets\Payment;
use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;


/**
 * Site controller
 */
class PaymentController extends Controller
{

    public function behaviors()
    {
        return [
//                'access' => [
//                'class' => AccessControl::className(),
//                'rules' => [
//                        [
//                            'actions' => ['index', 'fail'],
//                            'allow' => true,
//                            'roles' => ['?'],
//                        ],
//                    ],
//                ],
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'index' => ['post'],
                        'check' => ['post'],
                        'aviso' => ['post'],
                    ],
                ]
            ];
    }

    public function actionIndex(){
        Logger::payment('Index: ' . json_encode(Yii::$app->request->post()));

        return  Payment::widget([
            'order' => \common\models\billing\Payment::getOrderByID(
                Yii::$app->request->post('order')
            ),
            'userIdentity' => Yii::$app->user->identity,
            'data' => ['customParam' => 'value'],
            'paymentType' => ['AC' => 'С банковской карты']
        ]);

    }

    public function actionFail(){
        Logger::payment('Index: ' . json_encode(Yii::$app->request->get()));
        var_dump(Yii::$app->request->get());
//        return  Payment::widget([
//            'order' => \common\models\billing\Payment::getOrderByID(
//                Yii::$app->request->post('order')
//            ),
//            'userIdentity' => Yii::$app->user->identity,
//            'data' => ['customParam' => 'value'],
//            'paymentType' => ['AC' => 'С банковской карты']
//        ]);

    }



    public function actions()
    {
        return [
            'check' => [
                'class' => 'kroshilin\yakassa\actions\CheckOrderAction',
                'beforeResponse' => function ($request) {
                    /**
                     * @var \yii\web\Request $request
                     */

                    Logger::payment('check: ' . json_encode($request));

                    $invoice_id = (int) $request->post('orderNumber');
                    Yii::warning("Кто-то хотел купить несуществующую подписку! InvoiceId: {$invoice_id}", \Yii::$app->yakassa->logCategory);
                    return false;
                }
            ],
            'aviso' => [
                'class' => 'kroshilin\yakassa\actions\PaymentAvisoAction',
                'beforeResponse' => function ($request) {
                    Logger::payment('aviso: ' . json_encode($request));

                    /**
                     * @var \yii\web\Request $request
                     */
                }
            ],
//            'fail' => [
//                'class' => 'frontend\controllers\PaymentController',
//                'beforeResponse' => function ($request) {
//                    Logger::payment('Fail: ' . json_encode($request));
//
//                }
//            ]
        ];
    }

}