<?php

use yii\db\Migration;

/**
 * Class m171129_045456_HistoryPostDate
 */
class m171129_045456_HistoryPostDate extends Migration
{
    public function up()
    {
        $this->addColumn('history', 'post_date', $this->integer()->notNull());

    }

    public function down()
    {
        $this->dropColumn('history', 'post_date');
    }
}
