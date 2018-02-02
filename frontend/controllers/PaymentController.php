<?php

/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 31.01.2018
 * Time: 9:31
 */

namespace frontend\controllers;

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
        return  Payment::widget([
            'order' => \common\models\billing\Payment::getOrderByID(
                Yii::$app->request->post('order')
            ),
            'userIdentity' => Yii::$app->user->identity,
            'data' => ['customParam' => 'value'],
            'paymentType' => ['AC' => 'С банковской карты']
        ]);

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
                    $invoice_id = (int) $request->post('orderNumber');
                    Yii::warning("Кто-то хотел купить несуществующую подписку! InvoiceId: {$invoice_id}", Yii::$app->yakassa->logCategory);
                    return false;
                }
            ],
            'aviso' => [
                'class' => 'kroshilin\yakassa\actions\PaymentAvisoAction',
                'beforeResponse' => function ($request) {
                    /**
                     * @var \yii\web\Request $request
                     */
                }
            ],
        ];
    }

}