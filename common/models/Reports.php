<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use common\commands\command\GetPostsCommand;


/**
 * This is the model class for table "reports".
 *
 * @property integer $id
 * @property integer $mlg_id
 * @property integer $title
 * @property integer $active
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



    public static function getActiveIDs(){
        return ArrayHelper::getColumn(
            self::find()->where(['active'=>1])->asArray()->all(),
            'mlg_id'
        );
    }

    public static function getReports(){
        return \Yii::$app->commandBus->handle(
            new GetPostsCommand(
                \common\services\StaticConfig::ReportsConfig()
            )
        );
    }
}
