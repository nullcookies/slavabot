<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use common\models\ExtraPropsBehaviour;
use common\models\Social;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;


class SimpleHistory extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'history';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className()
        ];
    }

    public function getAccountValue()
    {
        return $this->hasOne(Accounts::className(), ['user_id' => 'user_id'])->where(['like', 'data', preg_replace("/[^0-9]/", '', json_decode($this->data, true)['wall_id'])]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'callback_tlg_message_status'], 'integer'],
            [['type', 'data'], 'string']

        ];
    }


    public function fields()
    {
        return [
            'data' => function(){
                return Json::decode($this->data);
            },
            'account' => 'accountValue',
            'type',
            'updated_at'
        ];
    }

}
