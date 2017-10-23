<?php

use yii\db\Migration;

class m171023_095448_change_user_field extends Migration
{
    public function up()
    {
        $this->dropIndex('username', 'user');
    }

    public function down()
    {

    }
}
