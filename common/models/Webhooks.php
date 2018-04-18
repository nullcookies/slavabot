<?php

namespace common\models;

use common\commands\command\FilterNotificationCommand;
use common\commands\command\ClearPostsCommand;
use Yii;
use yii\base\Exception;
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
     * Получаем страну поста
     *
     * Справочник common\models\ACountry, наполняется автоматически,
     * при добавлении поста
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDataCountry()
    {
        return $this->hasOne(ACountry::className(), [
            'id' => 'aCountry',
        ]);

    }

    /**
     * Получаем регион поста
     *
     * Справочник common\models\ARegion, наполняется автоматически,
     * при добавлении поста
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDataRegion()
    {
        return $this->hasOne(ARegion::className(), [
            'id' => 'aRegion',
        ]);

    }

    /**
     * Получаем город поста
     *
     * Справочник common\models\ACity, наполняется автоматически,
     * при добавлении поста
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDataCity()
    {
        return $this->hasOne(ACity::className(), [
            'id' => 'aCity',
        ]);

    }

    public function getDataFavorite()
    {
        return $this->hasOne(FavoritesPosts::className(), [
            'post_id' => 'id',
        ])->where(['user_id' => \Yii::$app->user->id]);

    }

    /**
     * Получаем категорию поста
     *
     * Справочник common\models\Reports, заполняется
     * и редактируется в админке (Главная > Выгрузки)
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDataCategory()
    {
        return $this->hasOne(Reports::className(), [
            'mlg_id' => 'category',
        ]);

    }

    /**
     * Получаем блог поста
     *
     * Справочник common\models\ABlog, наполняется автоматически,
     * при добавлении поста
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDataBlog()
    {
        return $this->hasOne(ABlog::className(), [
            'id' => 'blog',
        ]);

    }

    /**
     *
     * location - строка местоположения поста (страна, регион, город), формируется из справочников
     * categoryText - строка, название категории поста
     * blogName - массив, данные о блоге, к которому принадлежит пост (Titter, VK, Facebook и т.п.)
     *
     * @inheritdoc
     */
    public function fields(){
        return [
            'id',
            'post_id',
            'category',
            'aCity',
            'aCountry',
            'aRegion',
            'post_url',
            'author_image_url',
            'author_url',
            'post_content',
            'author_name',
            'blog',
            'type',
            'published_at',

            'location' => function(){
                if($this->dataCountry->aid==0 && $this->dataRegion==0 && $this->dataCity==0){
                    return false;
                }

                $res = '';

                if($this->dataCountry->aid!=0){

                    if($this->dataCountry->aType!=''){
                        $res .= $this->dataCountry->aType.' ';
                    }

                    $res.= $this->dataCountry->aName.', ';
                }

                if($this->dataRegion->aid!=0 && $this->dataRegion->aName!=$this->dataCity->aName){

                    if($this->dataRegion->aType!=''){
                        $res .= $this->dataRegion->aType.' ';
                    }

                    $res.= $this->dataRegion->aName.', ';
                }

                if($this->dataCity->aid!=0){

                    if($this->dataCity->aType!=''){
                        $res .= $this->dataCity->aType.' ';
                    }

                    $res.= $this->dataCity->aName;
                }else{
                    $res = substr($res, 0, -2);
                }

                return $res;
            },
            'categoryText' => function(){
                return $this->dataCategory->title;
            },
            'blogName' => function(){
                return $this->dataBlog;
            },
            'favorite' => function(){
                return ($this->dataFavorite) ? true : false;
            },
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

    /**
     * Сохранение нового поста.
     *
     *
     * @param $data => [
     *                  'post_id'          => ID - поста, унакальный для каждого поста
     *                  'category'         => ID категории (отчет из модели common\models\Reports)
     *                  'aCity'            => ID города (справочник common\models\ACity)
     *                  'aCountry'         => ID страны (справочник common\models\ACountry)
     *                  'aRegion'          => ID региона (справочник common\models\ARegion)
     *                  'post_url'         => Ссылка на пост
     *                  'author_image_url' => Аватар автора
     *                  'author_url'       => Ссылка на автора поста
     *                  'post_content'     => Содержимое поста (обычно html)
     *                  'author_name'      => Имя автора
     *                  'blog'             => тип социальной сети (справочник common\models\ABlog)
     *
     *                  'type'             => Тип поста (
     *                                          0 - оригинальный пост,
     *                                          1 - репост,
     *                                          2 - сообщение на форуме (но это не точно)
     *                                     )
     *
     *                  'published_at'     => дата публикации (timestamp, часовой пояс Europe/London)
     *                  ]
     *
     * @return array - массив результата обработки поступивших постов
     */

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
                
                try{
                    \Yii::$app->commandBus->handle(
                        new FilterNotificationCommand(
                            [
                                'item' => $webhook
                            ]
                        )
                    );
                }catch(\Exception $e){

                }

                return [
                    'new' => true,
                    'id' => $webhook->post_id,
                ];
            }

        }else{
            return [
                'new' => true,
                'id' => 'Data error!'
            ];
        }
    }

    /**
     * Возвращает данные для вывода страницы "Обсуждения" и "Избранные"
     *
     * @param bool $user_contacts - true вернет обсуждения, находящиеся в избраном у текущего пользователя
     *                              false вернет все доступные обсуждения
     * @return array [
     *                 'webhooks'  =>  массив постов,
     *                 'pages'     => инфа для формирования пагинации,
     *                 'location'  =>  справочник городов common\models\ACity, для фильтра
     *                 'theme'     =>  справочник категорий common\models\Reports, для фильтра
     *               ]
     */


    public static function getWebHooks($user_contacts = false)
    {

        $searchArr = [];
        $filter = [];


        if(Yii::$app->request->post()){
            $page = Yii::$app->request->post()['page'];
            $search = Yii::$app->request->post()['search'];
            $location = Yii::$app->request->post()['city'];
            $region = Yii::$app->request->post()['region'];
            $country =   Yii::$app->request->post()['country'];
            $theme = Yii::$app->request->post()['theme'];
        }else{
            $search ="";
            $page = 0;
            $location = 0;
            $theme = 0;
        }

        if($theme>0){
            $filter['category'] = $theme;
        }

        if($location>0){
            $filter['aCity'] = $location;
        }

        if($region>0){
            $filter['aRegion'] = $region;
        }

        if($country>0){
            $filter['aCountry'] = $country;
        }


        if(strlen($search)>3){
            $searchArr = array('LIKE', 'post_content', $search);
        }

        if($user_contacts){
            $webhooks = Webhooks::find()
                ->where([
                    'IN',
                    'id',
                    FavoritesPosts::GetPostsIDByUser()
                ])
                ->andWhere($filter)
                ->andWhere($searchArr)
                ->orderBy(['published_at' => SORT_DESC]);
        }else{
            $webhooks = Webhooks::find()
                ->where($filter)
                ->andWhere($searchArr)
                ->orderBy(['published_at' => SORT_DESC]);
        }


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
            'location'  =>  ACity::find()->asArray()->all(),
            'countries' => ACountry::find()->asArray()->all(),
            'regions' => ARegion::find()->asArray()->all(),
            'theme'     =>  Reports::getActive()
        );
    }

    public static function getDetail()
    {
        $webhooks = Webhooks::find()->where(['id' => Yii::$app->request->post()['id']])->one();

        return array(
            'webhooks'  =>  $webhooks
        );
    }

    /**
     * Вызов комманды для удаления устаревших постов
     *
     * @param $period - количество дней, после которого посты удаляются
     * @return mixed
     */
    public static function removeOldPosts($period){

        $result = \Yii::$app->commandBus->handle(
            new ClearPostsCommand(
                [
                    'period' => $period,
                ]
            )
        );

        return $result;
    }

}
