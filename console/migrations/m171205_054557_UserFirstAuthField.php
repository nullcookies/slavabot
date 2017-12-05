<?php

use yii\db\Migration;

/**
 * Class m171205_054557_UserFirstAuthField
 */
class m171205_054557_UserFirstAuthField extends Migration
{
    public function up()
    {
        $this->addColumn('user', 'authorized', $this->integer());

    }

    public function down()
    {
        $this->dropColumn('user', 'authorized');

    }
}
