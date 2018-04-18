<?php

/**
 * Консольная комманда для очистки устаревших постов.
 */

namespace console\controllers;

use common\services\StaticConfig;
use Yii;
use common\models\Webhooks;
use yii\console\Controller;


class ClearPostsController extends Controller
{

    /**
     *  Запуск очистки.
     *  Период берем из настроек common/config/config.yaml
     */
    public function actionStart()
    {
        $period = StaticConfig::clearPostsPeriod();
        Webhooks::removeOldPosts($period);
    }
}