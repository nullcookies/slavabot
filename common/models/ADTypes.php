<?php

/**
 * Модель для работы с типами контактов
 */

namespace common\models;

use Yii;

/**
 * Модель для работы с типами контактов
 *
 * @property integer $id
 * @property integer $mlg_id - идентификатор из медиалогии
 * @property integer $code - символьный код типа
 * @property string $name - Текстовое название типа
 */

class ADTypes  extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'additional_types';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mlg_id'], 'integer'],
            [['code'], 'string'],
            [['name'], 'string']

        ];
    }
}