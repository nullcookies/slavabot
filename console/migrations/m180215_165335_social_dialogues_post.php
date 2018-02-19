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
            'peer_id' => $this->bigInteger()->notNull(),
            'text' => $this->text()->notNull(),
            'attaches' => $this->text()->null(),
            'edited' => $this->integer(1)->notNull()->defaultValue(0),
            'hash' => $this->string()->null(),
            'related_post_id' => $this->integer()->null(),
            'created_at' => $this->timestamp()
        ]);

        $this->createIndex('user_id', 'social_dialogues_post', 'user_id');
        $this->createIndex('peer_id', 'social_dialogues_post', 'peer_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('user_id', 'social_dialogues_post');
        $this->dropIndex('peer_id', 'social_dialogues_post');
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
