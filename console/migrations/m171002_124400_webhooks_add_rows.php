<?php

use yii\db\Migration;

class m171002_124400_webhooks_add_rows extends Migration
{
    public function up()
    {

        $this->createTable('social_types', [
            'id' => $this->primaryKey(),
            'code' => $this->string()->notNull(),
            'name' => $this->string()->notNull()
        ]);
        $this->addColumn('webhooks', 'social', $this->string()->null());
        $this->addColumn('webhooks', 'created_at', $this->integer()->notNull());
    }

    public function down()
    {
        $this->dropTable('social_types');
        $this->dropColumn('webhooks', 'social');
        $this->dropColumn('webhooks', 'created_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171002_124400_webhooks_add_rows cannot be reverted.\n";

        return false;
    }
    */
}
