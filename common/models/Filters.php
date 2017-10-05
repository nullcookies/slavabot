<?php

namespace common\models;

use Yii;

/**
 * Модель для работы с клиентскими фильтрами по поиску потенциальных клиентов
 *
 * @property integer $id
 * @property integer $user_id - привязка к пользователю
 * @property string $name - пользовательское название фильтра
 * @property string $filter - содержимое фильтра в JSON
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

    /**
     * Возвращаем только имя и содержимое фильтра
     * @return array
     */
    public function fields()
    {
        return [
            'name',
            'filter'
        ];
    }

    /**
     * Получить все фильтры пользователя
     *
     * @return object - сохраненные фильтры |
     *         bool - в случае отсутсвия сохраненных фильтров
     */

    public static function getFilters()
    {
        $model = static::find()->where(['user_id' => Yii::$app->user->id])->asArray()->all();

        if($model){
            return $model;
        }else{
            return false;
        }
    }

    /**
     * Получить конкретный фильтр по его id
     *
     * @return object - найденный фильтр |
     *         bool - в случае отсутсвия фильтра
     */

    public static function getFilter($id)
    {
        $model = static::findOne(['id' => $id]);

        if($model){
            return $model;
        }else{
            return false;
        }
    }

    /**
     * Сохраняем новый фильтр
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
     * Обновляем существующий фильтр
     */

    public function updateFilter($item)
    {
        $model = Filters::findOne(['id' => $item['id']]);

        $model->name = $item['name'];
        $model->filter = $item['filter'];

        $model->save();

    }
}
