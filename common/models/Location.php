<?php

namespace common\models;

use Yii;

/**
 * Модель для работы с локациями. Наполняется при получении новых вебхуков.
 *
 * @property integer $id
 * @property integer $mlg_id - идентификатор медиалогии
 * @property string $name - текстовое название локации (города)
 */

class Location extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'location';
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
     * Проверка существования локаций с этим идентификатором медиалогии.
     *
     * @param $mlg_id - идентификатор медиалогии
     * @return bool - в случае отсутсвия
     *         int - id локации в случае наличия
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
     * Сохраняем поступившую локацию.
     *
     * @param $item - объект вебхука
     * @return bool - в случае пустого вебхука |
     *         int - id новой локации |
     *         int - id существующей локации
     */

    public static function saveReference($item)
    {
        $status = self::checkMLG($item->location->id);

        if($item->location->id){
            if(!self::checkMLG($item->location->id)){

                $loc = new Location();

                $loc->mlg_id = $item->location->id;
                $loc->name = $item->location->name;

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
