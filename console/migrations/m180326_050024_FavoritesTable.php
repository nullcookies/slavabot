<?php

use yii\db\Migration;

/**
 * Class m180326_050024_FavoritesTable
 */
class m180326_050024_FavoritesTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('favorites_posts', [
            'id' => $this->primaryKey(),
            'post_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('favorites_posts');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180326_050024_FavoritesTable cannot be reverted.\n";

        return false;
    }
    */
}
