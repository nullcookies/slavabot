<?php

use yii\db\Migration;

class m171030_083616_socialTable extends Migration
{
    public function safeUp()
    {
        $this->createTable('social', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'type' => $this->string()->notNull(),
            'data' => $this->string()->notNull(),
            'status' => $this->integer(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('social');
    }

}
