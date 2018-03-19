<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "aRegion".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $aName
 * @property string $aType
 */
class ARegion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'aRegion';
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

    public static function getRegion($aRegion)
    {
        if(is_array($aRegion) && (int)$aRegion['aId'] > 0){
            $aid = (int)$aRegion['aId'];
            $aName = $aRegion['aName'];
            $aType = $aRegion['aType'];
        }else{
            $aid = 0;
            $aName = 'Нет данных';
            $aType = '';
        }

        $region = self::findOne([
            'aid' => $aid
        ]);

        if($region){
            return $region->id;
        }else{

            $region = new self();

            $region->aid = $aid;
            $region->aName = $aName;
            $region->aType = $aType;

            $region->save(false);

            return $region->id;
        }
    }
}
