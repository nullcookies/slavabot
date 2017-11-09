<?php

use yii\db\Migration;

class m171109_042234_UsersTelegramField extends Migration
{
    public function up()
    {
        $this->addColumn('user', 'telegram_id', $this->integer()->null());

    }

    public function down()
    {
        $this->dropColumn('user', 'telegram_id');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171109_042234_UsersTelegramField cannot be reverted.\n";

        return false;
    }
    */
}
