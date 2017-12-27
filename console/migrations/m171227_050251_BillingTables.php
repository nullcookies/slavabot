<?php

use yii\db\Migration;

/**
 * Class m171227_050251_BillingTables
 */
class m171227_050251_BillingTables extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('slava_tariffs', [
            'id' => $this->primaryKey(),
            'title' => $this->string(300),
            'description' => $this->string(5000),
            'cost' => $this->float(),
            'constraints' => $this->string(),
            'active' => $this->integer()
        ]);

        $this->createTable('slava_payment', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'tariff_id' => $this->integer(),
            'begin' => $this->dateTime(),
            'expire' => $this->dateTime(),
            'active' => $this->integer(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('slava_tariffs');
        $this->dropTable('slava_payment');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171227_050251_BillingTables cannot be reverted.\n";

        return false;
    }
    */
}
