<?php
/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 19.03.2018
 * Time: 15:24
 *
 * Получение постов из sm.mlg.ru
 */


namespace console\controllers;

use common\models\Reports;
use frontend\controllers\bot\libs\Logger;
use Yii;
use yii\console\Controller;


class ReportsController extends Controller
{

    /**
     * Инициализируем подгрузку новых постов из sm.mlg.ru
     * Настройки интервала и адреса запросов:
     *
     * /common/config/config.yaml
     *
     * Обновляем посты, результат обработки данных хранится:
     *
     * /frontend/runtime/logs/reports.log
     */

    public function actionGetReports()
    {

        $reports = Reports::getReports();

        Logger::report('GetReports:', [
            'Reports' => $reports
        ]);

    }


}