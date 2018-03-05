<?php

namespace common\models;

use common\services\social\FacebookService;
use frontend\controllers\bot\libs\Logger;
use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use common\models\ExtraPropsBehaviour;
use common\models\Instagram;


class Accounts extends \yii\db\ActiveRecord
{
    const TYPE_FB = 'facebook';
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
            [['user_id', 'status', 'processed', 'fb_page'], 'integer'],
            [['type', 'data'], 'string']

        ];
    }

    public function getUserValue()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
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
                $data = json_decode($this->data);

                if(isset($data->password)){
                    $decrypt = \Yii::$app->encrypter->decrypt($data->password);
                    if($decrypt){
                        $data->password = $decrypt;
                    }
                }

                return $data;
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

        $model->user_id = \Yii::$app->user->id;
        $model->type = $item['type'];
        $model->data = json_encode($item['data']);
        $model->processed = $processed;
        $model->status = 1;
        return $model->save();
    }

    public static function setStatus($id, $status){
        $model = static::findOne(['id' => $id]);

        $model->status = $status;
        return $model->save();
    }

    public static function getVk(){
        $model = static::find()->where(['type' => 'vkontakte'])->all();
        return $model;
    }

    public static function getIg()
    {
        $models = static::find()
            ->where(
                [
                    'type' => 'instagram',
                    'status' => 1,
                    'processed' => 1
                ]
            )
            ->all();

        return $models;
    }

    public static function processAccount(){

        $post = \Yii::$app->request->post();

        $acc = static::find()->where(['id' => $post['id']])->one();

        if(isset($post['data']['password'])){
            $decrypt = \Yii::$app->encrypter->encrypt($post['data']['password']);

            if($decrypt){
                $post['data']['password'] = $decrypt;
            }
        }

        $subscribe = false;
        if($acc->type == static::TYPE_FB) {
            if(isset($post['data']['groups']['type']) && $post['data']['groups']['type'] == 'page') {
                $acc->fb_page = $post['data']['groups']['id'];
                $subscribe = true;
            }
        }

        $acc->data = json_encode($post['data']);
        $acc->processed = 1;

        if($acc->save()) {
           if($subscribe) {
               $serv = new FacebookService();
               $api = $serv->init();
               try {
                   $response = $serv->subscribePage($api, $post['data']['groups']['id'], $post['data']['groups']['access_token']);

                   Logger::info(json_encode($response));

                   if(isset($response['success']) && $response['success'] == true) {
                       $result = true;
                   } else {
                       $result = false;
                   }

               } catch (\Exception $e) {
                   Logger::info($e->getMessage());
                   $result = false;
               }
           } else {
               $result = true;
           }
        } else {
            $result = false;
        }



        return $result;
    }

    public static function updateAccount(){
        $post = \Yii::$app->request->post();
        $acc = Accounts::find()->where(['id' => $post['id']])->one();
        $acc->processed = 2;
        return $acc->save();
    }

    public static function getUnprocessedAccounts($type=''){
        $filter = [
            'user_id' => Yii::$app->user->id,
            'processed' => 0
        ];

        if($type!=''){
            $filter['type'] =  $type;
        }
        return Accounts::find()->where($filter)->one();
    }

    public static function getAccounts(){
        return Accounts::find()->where(['user_id' => Yii::$app->user->id])->all();
    }

    public static function getByUser($id){
        return Accounts::find()->where(['user_id' => $id])->all();
    }

    public static function getByUserId($id, $social)
    {
        return Accounts::find()
            ->andWhere(['user_id' => $id])
            ->andWhere(
                [
                    'type' => $social,
                    'status' => 1,
                    'processed' => 1
                ]
            )
            ->one();
    }

    public static function remove($id){
        return Accounts::find()->where(['id' => $id])->one()->delete();
    }

}
