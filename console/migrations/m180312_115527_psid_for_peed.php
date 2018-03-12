<?php

use yii\db\Migration;

/**
 * Class m180312_115527_psid_for_peed
 */
class m180312_115527_psid_for_peed extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('social_dialogues_peer', 'psid',
            $this->bigInteger()->null()->after('peer_id'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('social_dialogues_peer', 'psid');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180312_115527_psid_for_peed cannot be reverted.\n";

        return false;
    }
    */
}
