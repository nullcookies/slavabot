<?php

use yii\db\Migration;

class m171017_075728_notifications_fields_on_filters_update extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('filters', 'notification');
        $this->dropColumn('filters', 'filter');

        $this->addColumn('filters', 'location', $this->integer()->null());
        $this->addColumn('filters', 'search', $this->string()->null());
        $this->addColumn('filters', 'theme', $this->integer()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('filters', 'location');
        $this->dropColumn('filters', 'search');
        $this->dropColumn('filters', 'theme');

        $this->addColumn('filters', 'filter', $this->string()->null());
        $this->addColumn('filters', 'notification', $this->string()->null());
    }
}
