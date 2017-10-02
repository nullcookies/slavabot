<?php

use yii\db\Migration;

class m171002_040944_webhooks_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('webhooks', [
            'id' => $this->primaryKey(),
            'mlg_id' => $this->integer()->notNull(),
            'number' => $this->integer()->notNull(),
            'client' => $this->integer()->notNull(),
            'location' => $this->integer()->notNull(),
            'category' => $this->integer()->notNull(),
            'priority' => $this->integer()->notNull(),
            'theme' => $this->integer()->notNull(),
            'post_url' => $this->string()->notNull(),
            'post_content' => $this->string()->notNull(),
            'author_name' => $this->string()->notNull()
        ]);

        $this->createTable('additional_types', [
            'id' => $this->primaryKey(),
            'mlg_id' => $this->integer()->notNull(),
            'code' => $this->string()->notNull(),
            'name' => $this->string()->notNull()
        ]);
        $this->createTable('additional_parameters', [
            'id' => $this->primaryKey(),
            'type' => $this->integer()->notNull(),
            'webhook' => $this->integer()->notNull(),
            'value' => $this->string()->notNull()
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('webhooks');
        $this->dropTable('additional_types');
        $this->dropTable('additional_parameters');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171002_040944_webhooks_table cannot be reverted.\n";

        return false;
    }
    */
}
