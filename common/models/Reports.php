<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use common\commands\command\GetPostsCommand;


/**
 * This is the model class for table "reports".
 *
 * Отчеты sm.mlg.ru, для получения постов.
 *
 * @property integer $id
 * @property integer $mlg_id - id отчета в sm.mlg.ru
 * @property integer $title - название отчета (локальное)
 * @property integer $active - флаг активности, если 0, то отчет исключается из запроса
 */

class Reports extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'reports';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mlg_id', 'title'], 'required'],
            [['mlg_id', 'active'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mlg_id' => 'Mlg ID',
            'title' => 'Title',
            'active' => 'Active',
        ];
    }

    /**
     * Получаем массив ID'шников активных элементов,
     * для получения постов
     *
     * @return array
     */

    public static function getActiveIDs(){
        return ArrayHelper::getColumn(
            self::find()->where(['active'=>1])->asArray()->all(),
            'mlg_id'
        );
    }

    public static function getActive(){
        return self::find()->where(['active'=>1])->asArray()->all();
    }

    /**
     * Запуск комманды для получения постов из отчетов (активных в админке)
     *
     * @return mixed - результат обработки поступивших постов
     */

    public static function getReports(){
        return \Yii::$app->commandBus->handle(
            new GetPostsCommand(
                \common\services\StaticConfig::ReportsConfig()
            )
        );
    }
}
