<?php

use yii\db\Migration;

/**
 * Class m180321_040609_LocationFieldsUpd
 */
class m180321_040609_LocationFieldsUpd extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('aCity', 'aRegion', $this->integer()->null());
        $this->addColumn('aCity', 'aCountry', $this->integer()->null());
        $this->addColumn('aRegion', 'aCountry', $this->integer()->null());

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('aCity', 'aRegion');
        $this->dropColumn('aCity', 'aCountry');
        $this->dropColumn('aRegion', 'aCountry');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180321_040609_LocationFieldsUpd cannot be reverted.\n";

        return false;
    }
    */
}
