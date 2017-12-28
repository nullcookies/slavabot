<?php
namespace common\models\billing;


use \yii\db\ActiveRecord;
use yii\helpers\Json;
use common\models\User;


class Tariffs extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'slava_tariffs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['id', 'integer'],
            [['title', 'description', ], 'string']
        ];
    }

    public function fields(){
        return [
            'id',
            'title',
            'description',
            'cost',
            'constraints' => function(){
                return Json::decode($this->constraints);
            },
            'current' => function(){
                return User::currentTariff()->tariffValue->id == $this->id;
            },
            'expire' => function(){
                if(User::currentTariff()->tariffValue->id == $this->id){
                    return User::expireToString();
                }else{
                    return false;
                }
            },
            'color'
        ];
    }

    static function getList()
    {
        return self::find()
            ->where(['active' => 1])
            ->orderBy(['sort' => 'ACS'])
            ->all();
    }



}