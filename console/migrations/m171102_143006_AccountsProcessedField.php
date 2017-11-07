<?php

use yii\db\Migration;

class m171102_143006_AccountsProcessedField extends Migration
{
    public function up()
    {
        $this->addColumn('social', 'processed', $this->integer()->null());

    }

    public function down()
    {
        $this->dropColumn('social', 'processed');

    }
}
