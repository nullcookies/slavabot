<?php
namespace common\models\billing;

use yii\db\ActiveRecord;
use common\models\billing\Tariffs;


class Payment extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'slava_payment';
    }

    /**
     * @inheritdoc
     */

    public function getTariffValue()
    {
        return $this->hasOne(Tariffs::className(), ['id' => 'tariff_id'])->where(['active' => 1]);

    }

    public function fields(){
        return [
            'id',
            'user_id',
            'expire',
            'title' => function(){
                return $this->tariffValue;
            },
        ];
    }


    public function rules()
    {
        return [
            [['id', 'user_id', 'tariff_id'], 'integer']
        ];
    }

}