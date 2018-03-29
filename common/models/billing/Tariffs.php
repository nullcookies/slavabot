<?php

namespace common\models\billing;

use Yii;

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
}
