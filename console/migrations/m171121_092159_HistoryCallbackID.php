<?php

use yii\db\Migration;

/**
 * Class m171121_092159_HistoryCallbackID
 */
class m171121_092159_HistoryCallbackID extends Migration
{
    public function up()
    {
        $this->addColumn('history', 'callback_tlg_message_status', $this->integer()->notNull());

    }

    public function down()
    {
        $this->dropColumn('history', 'callback_tlg_message_status');
    }
}
