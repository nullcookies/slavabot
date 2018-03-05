<?php

use yii\db\Migration;

/**
 * Class m180305_121910_TextsTable
 */
class m180305_121910_TextsTable extends Migration
{
    public function safeUp()
    {
        $this->createTable('notifications_texts', [
            'id' => $this->primaryKey(),
            'type' => $this->integer()->notNull(),
            'text' => $this->string(10000)->notNull(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('notifications_texts');
    }

}
