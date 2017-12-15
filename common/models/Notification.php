<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 15.12.2017
 * Time: 17:12
 */

namespace common\models;


use yii\db\ActiveRecord;

/**
 * Class Notification
 * @package common\models
 *
 * @property integer $id
 * @property string $created_at
 * @property string $internal_uid
 * @property string $social
 * @property string $message
 * @property string $hash
 */
class Notification extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'table_notifications';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['id', 'integer'],
            [['created_at', 'internal_uid', 'social', 'message', 'hash'], 'string']
        ];
    }

    public function getNotifications()
    {
        static $a = [];

        if(!isset($a[$this->uid])) {
            $a = [];

            $sql = 'select hash from table_notifications where created_at>now()-interval 1 HOUR';
            $items = \Yii::$app->db->createCommand($sql)->queryAll();

            foreach ($items as $item) {
                $a[$this->uid][$item['hash']] = true;
            }

        }

        if(isset($a[$this->uid])) {
            return $a[$this->uid];
        }
        else {
            return false;
        }
    }

    /**
     * Проверяет есть ли такой хэш
     * @param $hash
     * @return bool
     */
    public function existNotification($hash) {
        $items = $this->getNotifications();

        if(isset($items[$hash])) {
            return true;
        }
        else {
            return false;
        }
    }

}