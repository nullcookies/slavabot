<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 14.12.2017
 */

namespace console\controllers;

use common\services\StaticConfig;
use Yii;
use common\models\Webhooks;
use yii\console\Controller;


class ClearPostsController extends Controller
{
    public function actionStart()
    {
        $period = StaticConfig::clearPostsPeriod();
        Webhooks::removeOldPosts($period);
    }
}