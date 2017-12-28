<?php
namespace common\models\billing;

use \yii\db\ActiveRecord;
use yii\helpers\Json;


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