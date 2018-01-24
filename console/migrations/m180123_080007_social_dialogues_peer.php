<?php

use yii\db\Migration;

/**
 * Class m180123_080007_social_dialogues_peer
 */
class m180123_080007_social_dialogues_peer extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('social_dialogues_peer', [
            'id' => $this->primaryKey(),
            'social' => $this->string(2)->notNull(),
            'type' => $this->string()->notNull(),
            'peer_id' => $this->bigInteger()->notNull(),
            'title' => $this->string()->notNull(),
            'avatar' => $this->string()->notNull(),
            'created_at' => $this->timestamp()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('social_dialogues_peer');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180123_080007_social_dialogues_peer cannot be reverted.\n";

        return false;
    }
    */
}
