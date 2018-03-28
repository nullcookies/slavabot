<?php

use yii\db\Migration;

/**
 * Class m180319_065417_NewWebhooksTable
 */
class m180319_065417_NewWebhooksTable extends Migration
{
    public function safeUp()
    {
        $this->dropTable('webhooks');

        $this->createTable('webhooks', [
            'id' => $this->primaryKey(),
            'post_id' => $this->string()->notNull(),

            'category' => $this->integer()->notNull(),

            'aCity' => $this->integer()->notNull(),
            'aCountry' => $this->integer()->notNull(),
            'aRegion' => $this->integer()->notNull(),

            'post_url' => $this->string()->notNull(),
            'author_image_url' => $this->string()->null(),
            'author_url' => $this->string()->null(),
            'post_content' => $this->string(10000)->notNull(),
            'author_name' => $this->string()->notNull(),

            'blog' => $this->integer()->notNull(),
            'type' => $this->integer()->notNull(),

            'published_at' => $this->integer()->notNull(),
        ]);

    }

    public function safeDown()
    {
        $this->dropTable('webhooks');

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
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180319_065417_NewWebhooksTable cannot be reverted.\n";

        return false;
    }
    */
}
