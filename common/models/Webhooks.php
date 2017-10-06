<?php

namespace common\models;

use Yii;

use common\models\Location;
use common\models\Category;
use common\models\Priority;
use common\models\Theme;
use common\models\Social;
use common\models\Additional;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use common\models\ExtraPropsBehaviour;
use yii\data\Pagination;


class Webhooks  extends \yii\db\ActiveRecord
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
            [['mlg_id', 'number', 'client', 'location', 'category', 'priority', 'theme', 'social', 'created_at'], 'integer'],
            [['post_url', 'post_content', 'author_name', 'author_image_url', 'author_url'], 'string'],
        ];
    }

    public function getLocationValue()
    {
        return $this->hasOne(Location::className(), ['id' => 'location']);
    }
    public function getSocialValue()
    {
        return $this->hasOne(Social::className(), ['id' => 'social']);
    }
    public function getThemeValue()
    {
        return $this->hasOne(Social::className(), ['id' => 'theme']);
    }
    public function getContactsValue()
    {
        return $this->hasMany(Additional::className(), ['webhook' => 'id']);
    }

    public function fields()
    {
        return [
            'mlg_id',
            'location' => function(){
                return (string)$this->location;
            },
            'theme' => function(){
                return (string)$this->theme;
            },
            'social',
            'locationValue' => 'locationValue',
            'post_content' => function(){
                $body = $this->post_content;
                $pattern = '~(\(\d{3}\)\s\d{3}\-\d{2}\-\d{2})|(\d{3}\s\d{3}\s\d{2}\s\d{2})|(\d{10})|(\d{3}\s\d{3}\-\d{2}\-\d{2})|(8\(\d{3}\)\s\d{3}\-\d{2}\-\d{2})|(7\s\d{3}\s\d{3}\-\d{2}\-\d{2})~s';
                $body = preg_replace($pattern, "<b>[номер телефона]</b>", $body);
                return $body;
            },
            'author_name',
            'author_image_url'=> function(){
                if(strlen($this->author_image_url)>0){
                    return $this->author_image_url;
                }else{
                    return 'https://orteka.ru/local/templates/.default/components/bitrix/news.detail/salon_item/images/no-photo.png';
                }
            },
            'tags' => function(){

                $res  = false;

                foreach($this->contactsValue as $contact){
                    if($contact->type==3){
                        $res  = explode(',', $contact->value);
                    }
                }
                return $res;
            },
            'author_url',
            'socialValue' => 'socialValue',
            'themeValue' => 'themeValue',
            'contacts' => 'contactsValue',
            'created_at'
        ];
    }

    public static function getWebHooks()
    {
        $filter = [];
        $searchArr = [];

        $page = Yii::$app->request->post()['page'];
        $search = Yii::$app->request->post()['search'];
        $location = Yii::$app->request->post()['city'];
        $theme = Yii::$app->request->post()['theme'];

        if($location>0){
            $filter['location'] = $location;
        }
        if($theme>0){
            $filter['theme'] = $theme;
        }
        if(strlen($search)>3){
            $searchArr = array('LIKE', 'post_content', $search);
        }

        $webhooks = Webhooks::find()->where($filter)->andWhere($searchArr)->orderBy(['created_at' => SORT_DESC]);

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
            'location'  =>  Location::find()->asArray()->all(),
            'category'  =>  Category::find()->asArray()->all(),
            'priority'  =>  Priority::find()->asArray()->all(),
            'theme'     =>  Theme::find()->asArray()->all()
        );
    }

    public static function checkWebHook($mlg_id)
    {
        $id = self::findOne(['mlg_id' => $mlg_id]);

        if($id){
            return $id;
        }else{
            return new Webhooks();
        }
    }

    public static function SaveWebHook($item)
    {
        $elem = self::checkWebHook($item->id);

        $elem->mlg_id =  $item->id;
        $elem->number = $item->number;
        $elem->client = $item->client;

        $elem->location = Location::saveReference($item);
        $elem->category = Category::saveReference($item);
        $elem->priority = Priority::saveReference($item);
        $elem->theme = Theme::saveReference($item);

        $elem->post_url = $item->post_url;
        $elem->author_image_url  = $item->author_image_url;
        $elem->author_url = $item->author_url;
        $elem->post_content = $item->post_content;
        $elem->author_name = $item->author_name;
        $elem->social = Social::checkSocial($item);
        $elem->created_at = (int)strtotime($item->created);

        $elem->save();

        Additional::saveReference($item, $elem->id);
    }
}