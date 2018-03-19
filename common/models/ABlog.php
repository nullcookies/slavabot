<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "aBlog".
 *
 * @property integer $id
 * @property string $aBlogHost
 * @property integer $aBlogHostId
 * @property integer $aBlogHostType
 */
class ABlog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'aBlog';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aBlogHost', 'aBlogHostId', 'aBlogHostType'], 'required'],
            [['aBlogHostId', 'aBlogHostType'], 'integer'],
            [['aBlogHost'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aBlogHost' => 'A Blog Host',
            'aBlogHostId' => 'A Blog Host ID',
            'aBlogHostType' => 'A Blog Host Type',
        ];
    }

    public static function getBlog($aBlogHost, $aBlogHostId, $aBlogHostType)
    {
        $blog = self::findOne([
            'aBlogHost' => $aBlogHost,
            'aBlogHostId' => $aBlogHostId,
            'aBlogHostType' => $aBlogHostType,
        ]);

        if($blog){
            return $blog->id;
        }else{
            $blog = new self();

            $blog->aBlogHost = $aBlogHost;
            $blog->aBlogHostId = $aBlogHostId;
            $blog->aBlogHostType = $aBlogHostType;

            $blog->save();

            return $blog->id;
        }
    }

}
