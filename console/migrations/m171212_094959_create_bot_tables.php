<?php

use yii\db\Migration;

/**
 * Class m171212_094959_create_bot_tables
 */
class m171212_094959_create_bot_tables extends Migration
{
    public function up()
    {
        $this->createTable('table_job_posts', [
            'id' => $this->primaryKey(),
            'internal_uid' => $this->string(),
            'social' => $this->string(),
            'post_id' => $this->string(),
            'schedule_dt' => $this->dateTime(),
            'execute_dt' => $this->dateTime(),
            'status' => $this->string(),
            'payload' => $this->text()
        ]);

        $this->createTable('table_notifications', [
            'id' => $this->primaryKey(),
            'created_at' => $this->timestamp(),
            'internal_uid' => $this->string(),
            'social' => $this->string(),
            'message' => $this->string(),
            'hash' => $this->string()
        ]);

        $this->createIndex('social', 'table_notifications', 'social');
        $this->createIndex('internal_uid', 'table_notifications', 'internal_uid');
        $this->createIndex('hash', 'table_notifications', 'hash');

        $this->createTable('table_posts', [
            'id' => $this->primaryKey(),
            'internal_uid' => $this->string(),
            'social' => $this->string(),
            'external_uid' => $this->string(),
            'callback_tlg_message_status' => $this->string(),
            'wall_id' => $this->string(),
            'video' => $this->text(),
            'photo' => $this->text(),
            'message' => $this->text(),
            'job_status' => $this->string(),
            'job_result' => $this->string(),
            'job_error' => $this->string()
        ]);

        $this->createTable('table_users', [
            'id' => $this->primaryKey(),
            'telegram_id' => $this->integer(),
            'email' => $this->string(100),
            'tzone' => $this->string(100)
        ]);

    }

    public function down()
    {
        $this->dropTable('table_posts');
        $this->dropTable('table_job_posts');
        $this->dropTable('table_notifications');
        $this->dropTable('table_users');
    }

}
