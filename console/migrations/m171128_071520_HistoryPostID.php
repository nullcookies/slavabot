<?php

use yii\db\Migration;

/**
 * Class m171128_071520_HistoryPostID
 */
class m171128_071520_HistoryPostID extends Migration
{
    public function up()
    {
        $this->addColumn('history', 'post_id', $this->integer()->notNull());

    }

    public function down()
    {
        $this->dropColumn('history', 'post_id');
    }
}
