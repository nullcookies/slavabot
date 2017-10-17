<?php

use yii\db\Migration;

class m171017_045853_notifications_fields_on_filters extends Migration
{
    public function safeUp()
    {
        $this->addColumn('filters', 'notification', $this->string()->null());
        $this->addColumn('filters', 'email', $this->string()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('filters', 'notification');
        $this->dropColumn('filters', 'email');

    }
}
