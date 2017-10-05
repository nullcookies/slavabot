<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "clubs".
 *
 * @property integer $id
 * @property integer $active
 * @property string $ru_name
 * @property string $en_name
 * @property string $ru_description
 * @property string $en_description
 * @property integer $sort
 * @property string $city
 * @property string $adress
 * @property integer $level
 * @property string $lat
 * @property string $lng
 * @property integer $created_at
 * @property integer $updated_at
 */
class Filters extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'filters';
    }

    /**
     * @inheritdoc
     */

    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['name', 'filter'], 'string']

        ];
    }

    public function fields()
    {
        return [
            'name',
            'filter'
        ];
    }

    /**
     * @inheritdoc
     */

    public static function getFilters()
    {
        $id = static::find()->where(['user_id' => Yii::$app->user->id])->asArray()->all();

        if($id){
            return $id;
        }else{
            return false;
        }
    }

    /**
     * @inheritdoc
     */

    public static function getFilter($id)
    {
        $id = static::findOne(['id' => $id]);

        if($id){
            return $id;
        }else{
            return false;
        }
    }

    /**
     * @inheritdoc
     */

    public function saveFilter($item)
    {
        $model = new Filters();

        $model->user_id = Yii::$app->user->id;
        $model->name = $item['name'];
        $model->filter = $item['filter'];

        $model->save();

    }

    /**
     * @inheritdoc
     */

    public function updateFilter($item)
    {
        $model = Filters::findOne(['id' => $item['id']]);

        $model->name = $item['name'];
        $model->filter = $item['filter'];

        $model->save();

    }
}
