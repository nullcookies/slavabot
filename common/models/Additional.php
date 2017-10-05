<?php

namespace common\models;

use Yii;
use common\models\ADTypes;

class Additional extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'additional_parameters';
    }

    /**
     * @inheritdoc
     */

    public function rules()
    {
        return [
            [['type'], 'integer'],
            [['webhook'], 'integer'],
            [['value'], 'string']

        ];
    }

    /**
     * @inheritdoc
     */

    public function attributeLabels()
    {
        return [
            'type' => 'type',
            'webhook' => 'webhook',
            'value' => 'value'
        ];
    }

    /**
     * @inheritdoc
     */

    public function getContactsType()
    {
        return $this->hasOne(ADTypes::className(), ['id' => 'type']);
    }

    /**
     * @inheritdoc
     */

    public function fields()
    {
        return [
                'type',
                'value',
                'name' => function(){
                    return $this->contactsType->name;
                }
            ];
    }

    /**
     * @inheritdoc
     */

    public static function checkReference($type, $webhook)
    {
        $id = static::findOne(['type' => $type, 'webhook' => $webhook]);

        if($id){
            return $id;
        }else{
            return new Additional();
        }
    }

    /**
     * @inheritdoc
     */

    public static function saveReference($item, $id)
    {

        if($item->category->id) {
            foreach ($item->additional_parameters as $code => $param) {

                $additional_type = ADTypes::checkMLG((int)$code);

                if($param) {
                    $loc = self::checkReference($additional_type, $id);

                    $loc->type = $additional_type;
                    $loc->webhook = $id;
                    $loc->value = $param;

                    $loc->save(false);

                    $res[] = $loc->id;
                }
            }
            return $res;
        }else {
            return false;
        }
    }
}
