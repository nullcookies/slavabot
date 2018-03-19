<?php

namespace common\models;

use Yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use common\models\ExtraPropsBehaviour;
use yii\data\Pagination;
/**
 * This is the model class for table "webhooks".
 *
 * @property integer $id
 * @property integer $post_id
 * @property integer $category
 * @property integer $aCity
 * @property integer $aCountry
 * @property integer $aRegion
 * @property string $post_url
 * @property string $author_image_url
 * @property string $author_url
 * @property string $post_content
 * @property string $author_name
 * @property integer $blog
 * @property integer $type
 * @property integer $published_at
 */
class Webhooks extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'webhooks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id', 'category', 'aCity', 'aCountry', 'aRegion', 'post_url', 'post_content', 'author_name', 'blog', 'type', 'published_at'], 'required'],
            [['post_id', 'category', 'aCity', 'aCountry', 'aRegion', 'blog', 'type', 'published_at'], 'integer'],
            [['post_url', 'author_image_url', 'author_url', 'author_name'], 'string', 'max' => 255],
            [['post_content'], 'string', 'max' => 10000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'post_id' => 'Post ID',
            'category' => 'Category',
            'aCity' => 'A City',
            'aCountry' => 'A Country',
            'aRegion' => 'A Region',
            'post_url' => 'Post Url',
            'author_image_url' => 'Author Image Url',
            'author_url' => 'Author Url',
            'post_content' => 'Post Content',
            'author_name' => 'Author Name',
            'blog' => 'Blog',
            'type' => 'Type',
            'published_at' => 'Published At',
        ];
    }

    public static function savePost($data){
        if($data['post_id'] > 0){

            $webhook = self::findOne([
                'post_id' => (int)$data['post_id']
            ]);

            if($webhook){

                return [
                    'new' => false,
                    'id' => $webhook->post_id
                ];

            }else{

                $webhook = new self();

                $webhook->post_id = $data['post_id'];
                $webhook->category = (int)$data['category'];
                $webhook->aCity = (int)$data['aCity'];
                $webhook->aCountry = (int)$data['aCountry'];
                $webhook->aRegion = (int)$data['aRegion'];
                $webhook->post_url = $data['post_url'];
                $webhook->author_image_url = $data['author_image_url'];
                $webhook->author_url = $data['author_url'];
                $webhook->post_content = $data['post_content'];
                $webhook->author_name = $data['author_name'];
                $webhook->blog = $data['blog'];
                $webhook->type = $data['type'];
                $webhook->published_at = $data['published_at'];


                $webhook->save(false);

                //return $webhook->id;
                return [
                    'new' => true,
                    'id' => $webhook->post_id,
                    'post' => $data['post_id']
                ];
            }

        }else{
            return [
                'new' => true,
                'id' => 'Data error!'
            ];
        }
    }

    public static function getWebHooks()
    {
        $filter = [];
        $searchArr = [];

        if(Yii::$app->request->post()){
            $page = Yii::$app->request->post()['page'];
            $search = Yii::$app->request->post()['search'];
          //  $location = Yii::$app->request->post()['city'];
            $theme = Yii::$app->request->post()['theme'];
        }else{
            $search ="";
            $page = 0;
           // $location = 0;
            $theme = 0;
        }



        if($theme>0){
            $filter['theme'] = $theme;
        }

        if(strlen($search)>3){
            $searchArr = array('LIKE', 'post_content', $search);
        }

        $webhooks = Webhooks::find()->where($filter)->andWhere($searchArr)->orderBy(['published_at' => SORT_DESC]);

        $countQuery = clone $webhooks;

        $pages = new Pagination(
            [
                'totalCount' => $countQuery->count(),
                'pageSize' => 10,
                'page' => ($page > 0 ? $page : 0 )
            ]
        );

        $pages->pageSizeParam = false;

        $models = $webhooks->offset($pages->offset)
            ->limit($pages->limit)
            ->all();

        return array(
            'webhooks'  =>  $models,
            'pages'     => $pages,
//            'location'  =>  Location::find()->asArray()->all(),
//            'category'  =>  Category::find()->asArray()->all(),
//            'priority'  =>  Priority::find()->asArray()->all(),
            'theme'     =>  Reports::find()->asArray()->all()
        );
    }
}
