<?php

use yii\db\Migration;

/**
 * Class m180315_102616_ReportsTable
 */
class m180315_102616_ReportsTable extends Migration
{
    public function safeUp()
    {
        $this->createTable('reports', [
            'id' => $this->primaryKey(),
            'mlg_id' => $this->integer()->notNull(),
            'title' => $this->string()->notNull(),
            'active' => $this->integer(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('reports');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180315_102616_ReportsTable cannot be reverted.\n";

        return false;
    }
    */
}
