<?php

use yii\db\Migration;

class m171004_081602_filters_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('filters', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->integer()->notNull(),
            'filter' => $this->string()->notNull()
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('filters');
    }
}
