<?php
/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 19.03.2018
 * Time: 15:24
 */


namespace console\controllers;

use common\models\Reports;
use frontend\controllers\bot\libs\Logger;
use Yii;
use yii\console\Controller;


class ReportsController extends Controller
{


    public function actionGetReports()
    {

        $reports = Reports::getReports();
        Logger::report('GetReports:', [
            'Reports' => $reports
        ]);

    }


}