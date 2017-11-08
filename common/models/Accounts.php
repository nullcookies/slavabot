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
            [['user_id', 'status', 'processed'], 'integer'],
            [['type', 'data'], 'string']

        ];
    }

    /**
     *
     * 'data' => json данные учетной записи
     *
     * 'processed' => 0 запись добавлена, но требуется пост-настройка
     *                1 запись полностью настроена
     *                2 запись в состоянии обновления (кнопка обновить, получаем новый токен и данные аккаунта)
     * @return array
     *
     */
    public function fields()
    {
        return [
            'id',
            'user_id',
            'type',
            'data' => function(){
                return json_decode($this->data);
            },
            'status',
            'processed'
        ];
    }

    public static function checkReference()
    {
        $id = static::findOne(['user_id' => Yii::$app->user->id, 'processed' => 2]);

        if($id){
            return $id;
        }else{
            return new Accounts();
        }
    }

    public static function saveReference($item, $processed = 1)
    {
        if($post = \Yii::$app->request->post('id')){
            $model = static::findOne(['id' => $post]);
        }else{
            $model = self::checkReference();
        }


        $model->user_id = Yii::$app->user->id;
        $model->type = $item['type'];
        $model->data = json_encode($item['data']);
        $model->processed = $processed;
        $model->status = 1;
        return $model->save();
    }

    public static function setStatus($user_id, $account_id, $status){
        $acc = Accounts::find()->where(['user_id' => $user_id, 'id' => $account_id])->one();

        $acc->status = (int)$status;

        return $acc->save();
    }

    public static function processAccount(){
        $post = \Yii::$app->request->post();

        $acc = Accounts::find()->where(['id' => $post['id']])->one();

        $acc->data = json_encode($post['data']);
        $acc->processed = 1;

        return $acc->save();
    }
    public static function updateAccount(){
        $post = \Yii::$app->request->post();
        $acc = Accounts::find()->where(['id' => $post['id']])->one();
        $acc->processed = 2;
        return $acc->save();
    }

    public static function getUnprocessedAccounts(){
        return Accounts::find()->where(['user_id' => Yii::$app->user->id, 'processed' => 0])->one();
    }

    public static function getAccounts(){
        return Accounts::find()->all();
    }

    public static function getByUser($id){
        return Accounts::find()->where(['user_id' => $id])->all();
    }

    public static function remove($id){
        return Accounts::find()->where(['id' => $id])->one()->delete();
    }

}
