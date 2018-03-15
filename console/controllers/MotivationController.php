<?php

namespace console\controllers;

use common\models\User;
use yii\console\Controller;

class MotivationController extends Controller
{
    public function actionSend(){
        return User::postingNotification(-10);
    }
}