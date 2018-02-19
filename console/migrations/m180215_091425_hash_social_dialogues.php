<?php

use yii\db\Migration;

/**
 * Class m180215_091425_hash_social_dialogues
 */
class m180215_091425_hash_social_dialogues extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('social_dialogues', 'hash', $this->string()->null()->after('attaches'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('social_dialogues', 'hash');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180215_091425_hash_social_dialogues cannot be reverted.\n";

        return false;
    }
    */
}
