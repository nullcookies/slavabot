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

    public static function checkMLG($mlg_id)
    {
        $id = static::findOne(['mlg_id' => $mlg_id]);

        if($id){
            return $id->id;
        }else{
            return false;
        }
    }

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
