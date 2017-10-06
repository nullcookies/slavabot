<?php

use yii\db\Migration;

class m171006_154941_new_webhooks_field_owner extends Migration
{
    public function safeUp()
    {
        $this->addColumn('webhooks', 'owner', $this->integer()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('webhooks', 'owner');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171006_154941_new_webhooks_field_owner cannot be reverted.\n";

        return false;
    }
    */
}
