<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use common\models\ExtraPropsBehaviour;


class Accounts extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'social';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'status'], 'integer'],
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
            },
            'status'
        ];
    }

    public static function saveReference($item)
    {
        $model = new Accounts();

        $model->user_id = Yii::$app->user->id;
        $model->type = $item['type'];
        $model->data = json_encode($item['data']);

        return $model->save();
    }

    public static function setStatus($user_id, $account_id, $status){
        $acc = Accounts::find()->where(['user_id' => $user_id, 'id' => $account_id])->one();

        $acc->status = (int)$status;

        return $acc->save();
    }

    public static function getAccounts(){
        return Accounts::find()->all();
    }

    public static function getByUser($id){
        return Accounts::find()->where(['user_id' => $id])->all();
    }

}
