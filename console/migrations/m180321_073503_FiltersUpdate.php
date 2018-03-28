<?php

use yii\db\Migration;

/**
 * Class m180321_073503_FiltersUpdate
 */
class m180321_073503_FiltersUpdate extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('filters', 'aRegion', $this->integer()->null());
        $this->addColumn('filters', 'aCountry', $this->integer()->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('filters', 'aRegion');
        $this->dropColumn('filters', 'aCountry');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180321_073503_FiltersUpdate cannot be reverted.\n";

        return false;
    }
    */
}
