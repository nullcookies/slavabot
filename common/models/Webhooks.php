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


    public static function getWebHooks()
    {
        return Webhooks::find()
            ->orderBy('created_at DESC')
            ->select([
                self::tableName().'.*',
                Location::tableName().'.name AS locationValue',
                Category::tableName().'.name AS categoryValue',
                Theme::tableName().'.name AS themeValue',
                Social::tableName().'.name AS socialValue',
            ])
            ->leftJoin(Location::tableName(), '`'.self::tableName().'`.`location` = `'.Location::tableName().'`.`id`')
            ->leftJoin(Category::tableName(), '`'.self::tableName().'`.`category` = `'.Category::tableName().'`.`id`')
            ->leftJoin(Theme::tableName(), '`'.self::tableName().'`.`theme` = `'.Theme::tableName().'`.`id`')
            ->leftJoin(Social::tableName(), '`'.self::tableName().'`.`social` = `'.Social::tableName().'`.`id`')
            ->asArray()
            ->all();
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