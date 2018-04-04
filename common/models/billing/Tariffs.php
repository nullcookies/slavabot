<?php

namespace common\models\billing;

use Yii;
use yii\helpers\Json;
use common\models\User;

/**
 * This is the model class for table "slava_tariffs".
 *
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property double $cost
 * @property string $constraints
 * @property integer $active
 * @property integer $displayed
 * @property string $color
 * @property integer $sort
 */
class Tariffs extends \yii\db\ActiveRecord
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
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
                $this->constraints = json_encode($this->constraints);
            return true;
        } else {
            return false;
        }
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->constraints = json_decode($this->constraints, true);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cost'], 'number'],
            [['active', 'displayed', 'sort'], 'integer'],
            [['title'], 'string', 'max' => 300],
            [['description'], 'string', 'max' => 5000],
            [['color'], 'string', 'max' => 255],
            [['constraints'], 'default']
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название',
            'description' => 'Описание',
            'cost' => 'Стоимость',
            'constraints' => 'Ограничения тарифа',
            'active' => 'Активный тариф',
            'displayed' => 'Тип тарифа',
            'color' => 'Фон',
            'sort' => 'Сортировка',
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
            'active' => function(){
                return User::currentTariff();
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
            ->andWhere(['displayed' => 1])
            ->orderBy(['sort' => 'ASC'])
            ->asArray()
            ->all();
    }

    static function getTariffByID($id)
    {
        return self::find()
            ->where(['active' => 1])
            ->andWhere(['id'=>$id])
            ->one();
    }
}
