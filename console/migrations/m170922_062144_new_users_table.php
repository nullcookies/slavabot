<?php

use yii\db\Migration;

class m170922_062144_new_users_table extends Migration
{
    public function up()
    {
        $this->addColumn('user', 'phone', $this->string()->null());

    }

    public function down()
    {
        $this->dropColumn('user', 'phone');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m170922_062144_new_users_table cannot be reverted.\n";

        return false;
    }
    */
}
