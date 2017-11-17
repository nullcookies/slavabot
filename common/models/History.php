<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use common\models\ExtraPropsBehaviour;


class History extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'history';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['type', 'data'], 'string']

        ];
    }


    public function fields()
    {
        return [
            'id',
            'user_id',
            'type',
            'data' => function(){
                return json_decode($this->data);
            }
        ];
    }



    public static function saveEvent($user_id, $type, $data)
    {

        $model = new History();

        $model->user_id = $user_id;
        $model->type = $type;
        $model->data = $data;

        return $model->save();
    }

}
