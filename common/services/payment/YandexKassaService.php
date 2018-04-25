<?php

namespace common\services\payment;

use http\Exception\BadMethodCallException;
use YandexCheckout\Client;
use YandexCheckout\Request\Payments\CreatePaymentResponse;
use YandexCheckout\Request\Payments\Payment\CreateCaptureResponse;
use yii\base\Component;

class YandexKassaService extends Component
{
    /** @var Client */
    protected $client;
    /** @var string */
    public $shopId;
    /** @var string */
    public $secretKey;
    /** @var string */
    public $returnUrl;
    /** @var CreatePaymentResponse */
    public $payment;

    /**
     * * Init component
     * @throws \yii\base\InvalidConfigException
     * @return void
     */
    public function init()
    {
        parent::init();
        if (!$this->shopId || !$this->secretKey || !$this->returnUrl) {
            throw new BadMethodCallException('Params not configured');
        }
        $this->client = new Client();
        $this->client->setAuth($this->shopId, $this->secretKey);
        $this->returnUrl = \Yii::$app->urlManager->getHostInfo() . $this->returnUrl;
    }

    /**
     * Create payment
     * @param $amount
     * @return $this
     */
    public function createPayment($amount, $orderId)
    {
        $this->payment = $this->client->createPayment(
            [
                'amount'              => [
                    'value'    => (float)$amount,
                    'currency' => 'RUB',
                ],
                'payment_method_data' => [
                    'type' => 'bank_card',
                ],
                'confirmation'        => [
                    'type'       => 'redirect',
                    'return_url' => $this->returnUrl,
                ],
                'metadata'            => [
                    'orderId' => $orderId
                ]
            ]
        );

        return $this;

    }

    /**
     * @return string
     */
    public function getConfirmationUrl()
    {
        return $this->payment->getConfirmation()->getConfirmationUrl();
    }

    /**
     * @param $data
     * @return CreateCaptureResponse
     */
    public function capturePayment($data)
    {
        $status = $data->event;

        if ($status === 'payment.waiting_for_capture') {
            return $result = $this->client->capturePayment([
                'amount' => $data->object->amount->value
            ], $data->object->id);
        }

    }

}