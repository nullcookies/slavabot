<?php

use yii\db\Migration;

/**
 * Class m180323_040118_FiltersNotification
 */
class m180323_040118_FiltersNotification extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropColumn('filters', 'email');
        $this->addColumn('filters', 'send_notification', $this->integer()->null());

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('filters', 'send_notification');
        $this->addColumn('filters', 'email', $this->string()->null());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180323_040118_FiltersNotification cannot be reverted.\n";

        return false;
    }
    */
}
