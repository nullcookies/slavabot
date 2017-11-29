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
use Carbon\Carbon;


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
            'postDate' => function(){
                $format = "dd.M.y HH:mm";

                if($this->updated_at-time() < 86400){
                    $corbon = "H:i";
                }else{
                    $corbon = "j.m.Y H:i";
                }

                $redate = \Yii::$app->formatter->asDatetime($this->updated_at, $format);

                if(Json::decode($this->data)['schedule_dt']){
                    $postDate = Json::decode($this->data)['schedule_dt'];
                    $date = $postDate['date'];
                    $timezone = $postDate['timezone'];
                    $redate = Carbon::parse($date, 'Europe/London');
                    $redate->setTimezone(\Yii::$app->user->identity->timezone);
                    $redate->setToStringFormat($corbon);
                    $redate = $redate->__toString();
                }else{
                    $redate = Carbon::parse($redate, 'Europe/Moscow');
                    $redate->setTimezone(\Yii::$app->user->identity->timezone);
                    $redate->setToStringFormat($corbon);
                    $redate = $redate->__toString();
                }
                return $redate;
            },
            'callback_tlg_message_status',
            'updated_at'
        ];
    }

    public static function saveEvent($user_id = 0, $type, $data)
    {

        $format = "dd.M.y HH:mm";

        if(json_decode($data, true)['post_id']){
            $model = History::findOne(['post_id' => json_decode($data, true)['post_id']]);
            $old_data = json_decode($model->data, true);
            $old_data['schedule_dt'] = json_decode($data, true)['schedule_dt'];

            $data = json_encode($old_data);
        }else{

            $model = History::findOne(
                [
                    'type' => $type,
                    'callback_tlg_message_status' => json_decode($data, true)['callback_tlg_message_status'],
                    'post_id' => json_decode($data, true)['id']
                ]
            );

            if(!$model){
                $model = new History();
                $tig = json_decode($data, true)['internal_uid'];

                $user = User::findByTIG($tig);

                $model->callback_tlg_message_status = json_decode($data, true)['callback_tlg_message_status'];
                $model->post_id = json_decode($data, true)['id'];

                $model->user_id = $user->id;
                $model->type = $type;
                $model->post_date = time();

            }else{
                $old_data = json_decode($model->data, true);
                $old_data['job_status'] = json_decode($data, true)['job_status'];
                $old_data['job_result'] = json_decode($data, true)['job_result'];
                $data = json_encode($old_data);
            }
        }

        $model->data = $data;

        return $model->save();
    }

    public static function getHistory()
    {
        $user_id = \Yii::$app->user->identity->id;

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
            ->andWhere(['<>', 'callback_tlg_message_status', 0 ])
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

    public static function getPlanned()
    {
        $user_id = \Yii::$app->user->identity->id;

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
            ->andWhere(['<>', 'callback_tlg_message_status', 0 ])
            ->andWhere(['like', 'data', 'QUEUED'])
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
