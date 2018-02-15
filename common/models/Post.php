<?php
/**
 * Created by PhpStorm.
 * User: Eric Mikhaelyan
 * Date: 13.12.2017
 * Time: 11:49
 */

namespace common\models;


use yii\db\ActiveRecord;

/**
 * Class Post
 * @package common\models
 *
 * @property integer $id
 * @property string $internal_uid
 * @property string $social
 * @property string $external_uid
 * @property string $callback_tlg_message_status
 * @property string $wall_id
 * @property string $video
 * @property string $photo
 * @property string $message
 * @property string $job_status
 * @property string $job_result
 * @property string $job_error
 */
class Post extends ActiveRecord
{
    const JOB_STATUS_NR = "NR"; // Не требуется добавления в очередь
    const JOB_STATUS_QUEUED = "QUEUED"; // Поставлено в очередь
    const JOB_STATUS_FAIL = "FAIL"; // Ошибка выполнения здачи
    const JOB_STATUS_POSTED = "POSTED"; // Опубликовано
    const SOCIAL_VK = "VK"; // Вконтакте
    const SOCIAL_FB = "FB"; // facebook
    const SOCIAL_IG = "IG";   // instagram

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'table_posts';
    }

    public function getDataComments()
    {
        return $this->hasMany(SocialDialogues::className(), ['post_id' => 'result_post_id']);
    }

    public function fields()
    {
        return [
            'callback_tlg_message_status',
            'external_uid',
            'id',
            'internal_uid',
            'job_error',
            'job_result',
            'job_status',
            'message',
            'photo',
            'social',
            'video',
            'wall_id',
            'comments' => "dataComments",
            'result_post_id'
        ];
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['id', 'integer'],
            [['internal_uid', 'social', 'external_uid', 'callback_tlg_message_status', 'wall_id', 'video', 'photo', 'message', 'job_status', 'job_result', 'job_error', 'result_post_id'], 'string']
        ];
    }
}