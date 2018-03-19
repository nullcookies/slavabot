<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "aCountry".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $aName
 * @property string $aType
 */
class ACountry extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'aCountry';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'aName', 'aType'], 'required'],
            [['aid'], 'integer'],
            [['aName', 'aType'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => 'Aid',
            'aName' => 'A Name',
            'aType' => 'A Type',
        ];
    }

    public static function getCountry($aCountry)
    {
        if(is_array($aCountry) && (int)$aCountry['aId'] > 0){
            $aid = (int)$aCountry['aId'];
            $aName = $aCountry['aName'];
            $aType = $aCountry['aType'];
        }else{
            $aid = 0;
            $aName = 'Нет данных';
            $aType = '';
        }

        $country = self::findOne([
            'aid' => $aid
        ]);

        if($country){
            return $country->id;
        }else{

            $country = new self();

            $country->aid = $aid;
            $country->aName = $aName;
            $country->aType = $aType;

            $country->save(false);

            return $country->id;
        }
    }
}
