<?php

namespace common\models;

use Yii;

use common\models\Location;
use common\models\Category;
use common\models\Priority;
use common\models\Theme;
use common\models\Additional;
use yii\helpers\ArrayHelper;


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
            [['mlg_id', 'number', 'client', 'location', 'category', 'priority', 'theme'], 'integer'],
            [['post_url', 'post_content', 'author_name'], 'string'],

        ];
    }

    public function getLocationValue()
    {
        return $this->hasOne(Location::className(), ['id' => 'location']);
    }
    public function getCategoryValue()
    {
        return $this->hasOne(Category::className(), ['id' => 'category']);
    }
    public function getPriorityValue()
    {
        return $this->hasOne(Priority::className(), ['id' => 'priority']);
    }
    public function getThemeValue()
    {
        return $this->hasOne(Theme::className(), ['id' => 'theme']);
    }

    public static function getWebHooks()
    {
        return Webhooks::find()->all();
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

        $elem->save();

        Additional::saveReference($item, $elem->id);
    }
}