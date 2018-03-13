<?php

namespace common\models;

use Yii;
use common\models\SocialDialogues;

/**
 * This is the model class for table "social_dialogues_post".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $account_id
 * @property string $social
 * @property string $post_id
 * @property integer $peer_id
 * @property string $text
 * @property string $attaches
 * @property integer $edited
 * @property string $hash
 * @property integer $related_post_id
 * @property string $created_at
 */
class SocialDialoguesPost extends \yii\db\ActiveRecord
{
    const SOCIAL_VK = "VK"; // Вконтакте
    const SOCIAL_FB = "FB"; // facebook
    const SOCIAL_IG = "IG"; // instagram



    public function getDataComments()
    {
        if(Yii::$app->session->has('peer_id')) {
            $peer_id = Yii::$app->session->get('peer_id');
        } else {
            $peer_id = 1;
        }

        if($this->social === static::SOCIAL_IG || $this->social === static::SOCIAL_FB){
            return $this->hasMany(SocialDialogues::className(), ['post_id' => 'post_id'])
                ->where(['peer_id'=>$peer_id])
                ->orderBy(['id' => SORT_ASC]);
        }
        if($this->social === static::SOCIAL_VK){
            return $this->hasMany(SocialDialogues::className(), ['social' => 'social'])
                ->where(['type'=>'comment'])
                ->andWhere(
                    ['OR',
                        ['peer_id' => $peer_id],
                        [ 'peer_id' => (int)$this->account_id]
                    ]
                )
                ->andWhere(['post_id' => explode( '_', $this->post_id)[1]])
                ->andWhere(['account_id' => $this->account_id])
                ->orderBy(['id' => SORT_ASC]);
        }

    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'social_dialogues_post';
    }


    public function fields()
    {
        return [
            'id',
            'user_id',
            'account_id',
            'social',
            'post_id',
            'url',
            'comments' => "dataComments"
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'account_id', 'social', 'post_id'], 'required'],
            [['user_id',], 'integer'],
            [['created_at'], 'safe'],
            [['account_id', 'social', 'post_id', 'hash','url', 'last_comment'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'account_id' => 'Account ID',
            'social' => 'Social',
            'post_id' => 'Post ID',
            'url' => 'Url',
            'updated_at' => 'Updated At',
        ];
    }

    public static function saveIgPost($user_id, $account_id, $post_id, $url, $last_comment)
    {
        $social = static::SOCIAL_IG;

        $model = static::find()
            ->andWhere(['account_id' => $account_id, 'post_id' => $post_id, 'social' => $social])
            ->one();

        if(!$model) {
            $model = new static;
            $model->social = $social;

            $model->user_id = $user_id;
            $model->account_id = $account_id;
            $model->post_id = $post_id;
            $model->url = $url;
        }
        $model->last_comment = $last_comment;
        if(!$model->save(false)) {
            var_dump($model->errors);
        }

        return $model;
    }

    public static function saveVkPost($user_id, $account_id, $post_id, $url, $last_comment)
    {
        $social = static::SOCIAL_VK;

        $model = static::find()
            ->andWhere(['account_id' => $account_id, 'post_id' => $post_id, 'social' => $social])
            ->one();

        if(!$model) {
            $model = new static;
            $model->social = $social;

            $model->user_id = $user_id;
            $model->account_id = $account_id;
            $model->post_id = $post_id;
            $model->url = $url;
        }
        $model->last_comment = $last_comment;


        $model->url = $url;


        if(!$model->save(false)) {
            var_dump($model->errors);
        }

        return $model;
    }

    public static function saveFbPost($user_id, $account_id, $post_id, $url, $last_comment)
    {
        $social = static::SOCIAL_FB;

        $model = static::find()
            ->andWhere(['account_id' => $account_id, 'post_id' => $post_id, 'social' => $social])
            ->one();

        if(!$model) {
            $model = new static;
            $model->social = $social;

            $model->user_id = $user_id;
            $model->account_id = $account_id;
            $model->post_id = $post_id;
            $model->url = $url;
        }
        $model->last_comment = $last_comment;


        $model->url = $url;


        if(!$model->save(false)) {
            var_dump($model->errors);
        }

        return $model;
    }
}
