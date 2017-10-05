<?php

namespace common\models;

use Yii;

/**
 * Модель для работы с приоритетами. Наполняется при получении новых вебхуков.
 *
 * @property integer $id
 * @property integer $mlg_id - идентификатор медиалогии
 * @property string $name - текстовое название приоритетами
 */

class Priority extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'priority';
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
     * Проверка существования приоритета с этим идентификатором медиалогии.
     *
     * @param $mlg_id - идентификатор медиалогии
     * @return bool - в случае отсутсвия
     *         int - id приоритета в случае наличия
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
     * Сохраняем поступивший приоритет.
     *
     * @param $item - объект вебхука
     * @return bool - в случае пустого вебхука |
     *         int - id нового приоритета |
     *         int - id существующго приоритета
     */
    public static function saveReference($item)
    {
        $status = self::checkMLG($item->priority->id);

        if($item->priority->id){
            if(!self::checkMLG($item->priority->id)){

                $loc = new Priority();

                $loc->mlg_id = $item->priority->id;
                $loc->name = $item->priority->name;

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
