<?php

use yii\db\Migration;

class m171115_042104_HistoryTable extends Migration
{
    public function safeUp()
    {
        $this->createTable('history', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'type' => $this->string()->notNull(),
            'data' => $this->string(10000)->notNull(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('history');
    }
}
