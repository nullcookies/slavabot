<?php
/**
 * Created by PhpStorm.
 * User: lexgorbachev
 * Date: 02.10.17
 * Time: 17:56
 */

namespace common\models;

/**
 * Модель для работы с типами социальных сетей.
 *
 * @property integer $id
 * @property integer $code - символьный код, для идентификации социальной сети по ссылке на пост
 * @property string $name - текстовое название социальной сети
 */

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
     * Идентифицируем социальную сеть по ссылке на пост.
     *
     * @param $str - ссылке на пост (из вебхука)
     * @return int - идентификатор социальной сети
     *         0 - сеть не найдена в базе salesbot
     */
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