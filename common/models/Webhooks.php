<?php

namespace common\models;

use Yii;

use common\models\Location;
use common\models\Category;
use common\models\Priority;
use common\models\Theme;
use common\models\Social;
use common\models\Additional;
use yii\helpers\ArrayHelper;
use common\models\ExtraPropsBehaviour;


class Webhooks  extends \yii\db\ActiveRecord
{

    public function getClient(){
        return $this->client+5;
    }


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
            [['post_url', 'post_content', 'author_name'], 'string'],
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
            'socialValue' => 'socialValue',
            'themeValue' => 'themeValue',
            'created_at'
        ];
    }

    public static function getWebHooks()
    {

        return array(
            'webhooks'  =>  Webhooks::find()->orderBy(['created_at' => SORT_DESC])->all(),
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
        $elem->post_content = $item->post_content;
        $elem->author_name = $item->author_name;
        $elem->social = Social::checkSocial($item);
        $elem->created_at = (int)strtotime($item->created);

        $elem->save();

        Additional::saveReference($item, $elem->id);
    }
}