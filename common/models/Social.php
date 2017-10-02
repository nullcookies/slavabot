<?php
/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 02.10.17
 * Time: 17:56
 */

namespace common\models;


class Social extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'social_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code'], 'string'],
            [['name'], 'string']

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'name' => 'Name'
        ];
    }

    public static function checkSocial($str)
    {
        $socs = static::find()->all();
        $res = 0;
        foreach($socs as $soc){
            if(strripos($str->post_url, $soc->code)){
                $res = $soc->id;
            }
        }
        return $res;
    }
}