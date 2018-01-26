<?php

namespace frontend\controllers;

use common\models\SocialDialogues;
use common\models\SocialDialoguesPeer;
use Yii;
use yii\data\Pagination;
use yii\db\Expression;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;


class NotificationController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only'  => [],
                'rules' => [
                    [
                        'actions' => ['notifications'],
                        'allow'   => true,
                        'roles'   => ['?'],
                    ],
                    [
                        'actions' => [],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'vk-auth' => ['post'],
                ],
            ],
            [
                'class'   => \yii\filters\ContentNegotiator::className(),
                'only'    => [
                    'notifications',
                    'user-notifications'
                ],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    public function actionNotifications()
    {
        $page = 0;

        if(Yii::$app->request->post()){
            $page = Yii::$app->request->post()['page'];
        }

        $subQuery = SocialDialogues::find()
            ->select(new Expression('max(sd.id)'))
            ->from(['sd' => SocialDialogues::tableName()])
            ->where(
                ['and',
                    ['sd.peer_id' => new Expression('social_dialogues.peer_id')],
                    ['user_id' => \Yii::$app->user->identity->id]
                ]);

        $query = SocialDialogues::find()
            ->where(['in', 'id', $subQuery])
            ->orderBy(['id' => SORT_DESC]);

        //$models = $query->all();

        $countQuery = clone $query;

        $pages = new Pagination(
            [
                'totalCount' => $countQuery->count(),
                'pageSize' => 10,
                'page' => ($page > 0 ? $page : 0 )
            ]
        );

        $pages->pageSizeParam = false;

        $models = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return [
            'notifications'  =>  $models,
            'pages'     => $pages,
        ];

        //return $models;

    }

    public function actionUserNotifications()
    {
        return [
            'user' => \Yii::$app->user->identity->id,
            'peer' => SocialDialoguesPeer::find()
            ->where(['id'=> Yii::$app->request->post('id')])
            ->orderBy(['id' => SORT_DESC])
            ->all()
        ];
    }


}