<?php

namespace common\models;

use Yii;

/**
 * Модель для работы с категориями. Наполняется при получении новых вебхуков.
 *
 * @property integer $id
 * @property integer $mlg_id - идентификатор из медиалогии
 * @property string $name - Текстовое название категории
 */

class Category extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'category';
    }

    /**
     * @inheritdoc
     */

    public function rules()
    {
        return [
            [['mlg_id'], 'integer'],
            [['name'], 'string']

        ];
    }

    /**
     * @inheritdoc
     */

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mlg_id' => 'ID MLG',
            'name' => 'Name'
        ];
    }

    /**
     * Проверяем категорию на существование.
     *
     * @param $mlg_id - идентификатор из медиалогии
     * @return bool - в случае отсутсвия |int - идентификатор salesbot найденной категории
     */

    public static function checkMLG($mlg_id)
    {
        $id = static::findOne(['mlg_id' => $mlg_id]);

        if($id){
            return $id->id;
        }else{
            return false;
        }
    }

    /**
     * Сохраняем новую категорию / Возвращаем id существующей
     *
     * @param $item - содержимое вэбхука
     *
     * @return bool - в случае отсутсвия содержимого вебхука |
     *         int - id существующей категории |
     *         int - id новой категории
     */

    public static function saveReference($item)
    {
        $status = self::checkMLG($item->category->id);

        if($item->category->id){
            if(!self::checkMLG($item->category->id)){

                $loc = new Category();

                $loc->mlg_id = $item->category->id;
                $loc->name = $item->category->name;

                $loc->save();

                return $loc->id;
            }else{
                return $status;
            }
        }else{
            return false;
        }
    }
}
