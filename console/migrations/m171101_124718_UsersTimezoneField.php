<?php

use yii\db\Migration;

class m171101_124718_UsersTimezoneField extends Migration
{
    public function up()
    {
        $this->addColumn('user', 'timezone', $this->string()->null());

    }

    public function down()
    {
        $this->dropColumn('user', 'timezone');

    }
}
