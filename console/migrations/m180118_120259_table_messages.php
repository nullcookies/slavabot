<?php

use yii\db\Migration;

/**
 * Class m180118_120259_table_messages
 *
 * Таблица для переписок
 */
class m180118_120259_table_messages extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable("social_dialogues", [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'social' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'peer_id' => $this->bigInteger()->notNull(),
            'peer_title' => $this->string()->notNull(),
            'message' => $this->text()->notNull(),
            'created_at' => $this->timestamp()
        ]);

        $this->createIndex('user_id', 'social_dialogues', 'user_id');
        $this->createIndex('peer_id', 'social_dialogues', 'peer_id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropIndex('user_id', 'social_dialogues');
        $this->dropIndex('peer_id', 'social_dialogues');
        $this->dropTable('social_dialogues');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180118_120259_table_messages cannot be reverted.\n";

        return false;
    }
    */
}
