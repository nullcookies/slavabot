<?php

use yii\db\Migration;

/**
 * Class m180228_064135_PostTableLastCommentID
 */
class m180228_064135_PostTableLastCommentID extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('social_dialogues_post', 'last_comment', $this->string()->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('social_dialogues_post', 'last_comment');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180228_064135_PostTableLastCommentID cannot be reverted.\n";

        return false;
    }
    */
}
