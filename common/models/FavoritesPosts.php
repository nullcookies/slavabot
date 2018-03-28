<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "favorites_posts".
 *
 * @property integer $id
 * @property integer $post_id
 * @property integer $user_id
 */
class FavoritesPosts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'favorites_posts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id', 'user_id'], 'required'],
            [['post_id', 'user_id'], 'integer'],
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
            'user_id' => 'User ID',
        ];
    }

    public static function GetPost($id)
    {
        $favorite = new self();

        $favorite->post_id = $id;
        $favorite->user_id = Yii::$app->user->id;

        return $favorite->save();
    }

    public static function DropPost($id)
    {
        $favorite = self::findOne(
            [
                'post_id' => $id,
                'user_id' => Yii::$app->user->id
            ]
        );

        return $favorite->delete();
    }

    public static function GetPostTLG($id, $tid, $return_message=false)
    {
        $favorite = new self();

        $favorite->post_id = $id;
        $favorite->user_id = User::findByTIG($tid)->id;
        $save = $favorite->save();

        if($return_message){
            return  "Пост добавлен в избранное:\n\n".strip_tags(Webhooks::findOne(['id'=>$id])->post_content);
        }

        return $save;
    }

    public static function GetPostsIDByUser(){

        return ArrayHelper::getColumn(
            self::find()->where(
                    [
                        'user_id' => Yii::$app->user->id
                    ]
                )
                ->asArray()
                ->all(),
            'post_id'
        );

    }


}
