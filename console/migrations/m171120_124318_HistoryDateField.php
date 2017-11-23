<?php

use yii\db\Migration;

/**
 * Class m171120_124318_HistoryDateField
 */
class m171120_124318_HistoryDateField extends Migration
{
    public function up()
    {
        $this->addColumn('history', 'created_at', $this->integer()->notNull());
        $this->addColumn('history', 'updated_at', $this->integer()->notNull());

    }

    public function down()
    {
        $this->dropColumn('history', 'created_at');
        $this->dropColumn('history', 'updated_at');

    }
}
