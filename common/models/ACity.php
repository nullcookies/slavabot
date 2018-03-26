<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "aCity".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $aName
 * @property string $aType
 */
class ACity extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'aCity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'aName'], 'required'],
            [['aid', 'aCountry', 'aRegion'], 'integer'],
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
            'aCountry' => 'A Country',
            'aRegion' => 'A Region'
        ];
    }

    public static function getCity($aCity, $aCountry, $aRegion)
    {
        if(is_array($aCity) && (int)$aCity['aId'] > 0){
            $aid = (int)$aCity['aId'];
            $aName = $aCity['aName'];
            $aType = $aCity['aType'];
        }else{
            $aid = 0;
            $aName = 'Нет данных';
            $aType = '';
        }

        $city = self::findOne([
            'aid' => $aid,
            'aCountry' => $aCountry,
            'aRegion' => $aRegion
        ]);

        if($city){
            return $city->id;
        }else{

            $city = new self();

            $city->aid = $aid;
            $city->aName = $aName;
            $city->aType = $aType;
            $city->aCountry = $aCountry;
            $city->aRegion = $aRegion;

            $city->save(false);

            return $city->id;
        }
    }
}
