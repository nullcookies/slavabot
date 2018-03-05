<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 14.12.2017
 */

namespace console\controllers;

use Yii;
use common\models\User;
use yii\console\Controller;


class CheckController extends Controller
{


    public function actionStart()
    {
        User::notification(1);
        User::notification(3);
        User::notification(5);


    }


}