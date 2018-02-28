<?php

namespace frontend\controllers;

use common\services\social\FbMessagesService;
use frontend\controllers\bot\libs\Logger;
use Yii;
use Yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class FbController extends \yii\web\Controller
{
    public function beforeAction($action)
    {
        if (in_array($action->id, ['fb-messages'])) {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        return 'OK';
    }

    public function actionFbMessages()
    {
        // Your verify token. Should be a random string.
        $verify_token = "salesbot_test";

        $mode = Yii::$app->request->get('hub_mode');
        $token = Yii::$app->request->get('hub_verify_token');
        $challenge = Yii::$app->request->get('hub_challenge');

        if ($mode && $token) {
            if ($mode === 'subscribe' && $token === $verify_token) {
                return $challenge;
            } else {
                throw new ForbiddenHttpException();
            }
        }





        Logger::info(Yii::$app->request->getRawBody());

        $body = json_decode(Yii::$app->request->getRawBody(), true);

        if ($body['object'] === 'page') {

            return 'EVENT_RECEIVED';
        } else {
            throw new NotFoundHttpException();
        }
    }

}
