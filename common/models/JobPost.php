<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 13.12.2017
 * Time: 11:38
 */

namespace common\models;


use yii\db\ActiveRecord;

/**
 * Class JobPost
 * @package common\models
 *
 * @property integer $id
 * @property string $internal_uid
 * @property string $social
 * @property string $post_id
 * @property string $schedule_dt
 * @property string $execute_dt
 * @property string $status
 * @property string $payload
 */
class JobPost extends ActiveRecord
{
    const JOB_STATUS_QUEUED = "QUEUED"; // Поставлено в очередь
    const JOB_STATUS_FAIL = "FAIL"; // Ошибка выполнения здачи
    const JOB_STATUS_POSTED = "EXECUTE"; // Опубликовано

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'table_job_posts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['id', 'integer'],
            [['internal_uid', 'social', 'post_id', 'schedule_dt', 'execute_dt', 'status', 'payload'], 'string']
        ];
    }

}