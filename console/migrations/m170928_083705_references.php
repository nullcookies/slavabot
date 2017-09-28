<?php

use yii\db\Migration;

class m170928_083705_references extends Migration
{
    public function safeUp()
    {
        $this->createTable('location', [
            'id' => $this->primaryKey(),
            'mlg_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull()
        ]);

        $this->createTable('category', [
            'id' => $this->primaryKey(),
            'mlg_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull()
        ]);

        $this->createTable('priority', [
            'id' => $this->primaryKey(),
            'mlg_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull()
        ]);

        $this->createTable('theme', [
            'id' => $this->primaryKey(),
            'mlg_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull()
        ]);

        $this->createTable('author_category', [
            'id' => $this->primaryKey(),
            'mlg_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull()
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('location');
        $this->dropTable('category');
        $this->dropTable('priority');
        $this->dropTable('theme');
        $this->dropTable('author_category');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170928_083705_references cannot be reverted.\n";

        return false;
    }
    */
}
