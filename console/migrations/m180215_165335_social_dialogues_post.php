<?php

use yii\db\Migration;

/**
 * Class m180215_165335_social_dialogues_post
 */
class m180215_165335_social_dialogues_post extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable("social_dialogues_post", [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'account_id' => $this->string()->notNull(),
            'social' => $this->string()->notNull(),
            'post_id' => $this->string()->notNull(),
            'url' => $this->string(8000),
            'updated_at' => $this->timestamp()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('social_dialogues_post');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180215_165335_social_dialogues_post cannot be reverted.\n";

        return false;
    }
    */
}
