<?php

use yii\db\Migration;

class m171109_081627_UsersAPIPasswordField extends Migration
{
    public function up()
    {
        $this->addColumn('user', 'temp_password_hash', $this->string()->null());

    }

    public function down()
    {
        $this->dropColumn('user', 'temp_password_hash');

    }

}
