<?php

use yii\db\Migration;

/**
 * Class m180214_150537_owner_post_for_social_dialogues
 */
class m180214_150537_owner_post_for_social_dialogues extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('social_dialogues', 'account_id', $this->string()->null()->after('user_id'));

        $this->addColumn('social_dialogues', 'post_id', $this->string()->null()->after('type'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('social_dialogues', 'account_id');

        $this->dropColumn('social_dialogues', 'post_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180214_150537_owner_post_for_social_dialogues cannot be reverted.\n";

        return false;
    }
    */
}
