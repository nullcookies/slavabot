<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use common\models\ExtraPropsBehaviour;
use common\models\Social;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Json;
use common\models\SimpleHistory;
use yii\data\Pagination;



class History extends \yii\db\ActiveRecord
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

    public function getDataValue()
    {
        $rel = $this->hasMany(SimpleHistory::className(), ['user_id' => 'user_id'])->where(['callback_tlg_message_status' => $this->callback_tlg_message_status]);
        return $rel;
    }

    public function fields()
    {
        return [
            'id',
            'user_id',
            'elements' => 'dataValue',
            'data' => function(){
                return Json::decode($this->data);
            },
            'callback_tlg_message_status',
            'updated_at'
        ];
    }

    public static function saveEvent($user_id = 0, $type, $data)
    {

        $model = new History();
        $tig = json_decode($data, true)['internal_uid'];
        $user = User::findByTIG($tig);

        $model->callback_tlg_message_status = json_decode($data, true)['callback_tlg_message_status'];
        $model->user_id = $user->id;
        $model->type = $type;
        $model->data = $data;

        return $model->save();
    }

    public static function getHistory()
    {
        $user_id = 19;//\Yii::$app->user->identity->id;

        $arOrder = [
            'asc' =>  SORT_ASC,
            'desc' =>  SORT_DESC,
        ];

        if(Yii::$app->request->post()){
            $page = Yii::$app->request->post()['page'];
            $order = $arOrder[Yii::$app->request->post()['order']];
        }else{
            $page = 0;
            $order = SORT_DESC;
        }

        $history = History::find()
            ->where(['user_id' => $user_id])
            ->groupBy('callback_tlg_message_status')
            ->orderBy(['updated_at' => $order]);

        $countQuery = clone $history;

        $pages = new Pagination(
            [
                'totalCount' => $countQuery->count(),
                'pageSize' => 10,
                'page' => ($page > 0 ? $page : 0 )
            ]
        );

        $pages->pageSizeParam = false;

        $models = $history->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return [
            'history'  =>  $models,
            'pages'     => $pages,
            'debug' => [
                $order
            ]
        ];
    }

}
