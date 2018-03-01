<?php

namespace frontend\controllers;

use common\models\Post;
use common\models\SocialDialogues;
use common\models\SocialDialoguesPeer;
use common\models\SocialDialoguesPost;
use Yii;
use yii\data\Pagination;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
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
                    ['sd.social' => new Expression('social_dialogues.social')],
                    ['direction' => 1],
                    ['user_id' => \Yii::$app->user->identity->id]
                ]);

        $query = SocialDialogues::find()
            ->where(['in', 'id', $subQuery])
            ->andWhere(['user_id' => \Yii::$app->user->identity->id])
            ->orderBy(['id' => SORT_DESC]);

        //$models = $query->all();
        $sql = $query->createCommand()->rawSql;
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
            'pages'          => $pages,
        ];

        //return $models;

    }

    public function actionUserNotifications()
    {
        $id = Yii::$app->request->post('id');

        if(!$id){
            return false;
        }

        $peer = SocialDialoguesPeer::find()
            ->where(['id'=> Yii::$app->request->post('id')])
            ->one();

        $posts = SocialDialoguesPost::find()
            ->where([
                    'IN',
                    'post_id',
                    ArrayHelper::getColumn(
                        SocialDialogues::getPostsByPeerAction($peer->peer_id),
                        'filter'
                    )
                ])
            ->orderBy(['updated_at' => SORT_DESC])
            ->all();

        $access = $peer->getMessagesCount()>0 || count($posts)>0;

        if(!$access){
            return [
                'user' => \Yii::$app->user->identity->id,
                'access' => $access
            ];
        }

        return [
            'user' => \Yii::$app->user->identity->id,
            'peer' => $peer,
            'posts' => $posts,
            'access' => $access,
        ];


    }


}