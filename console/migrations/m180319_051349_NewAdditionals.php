<?php

use yii\db\Migration;

/**
 * Class m180319_051349_NewAdditionals
 */
class m180319_051349_NewAdditionals extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropTable('location');
        $this->dropTable('category');
        $this->dropTable('priority');
        $this->dropTable('theme');
        $this->dropTable('author_category');
        $this->dropTable('social_types');


        $this->createTable('aCity', [
            'id' => $this->primaryKey(),
            'aid' => $this->integer()->notNull(),
            'aName' => $this->string()->notNull(),
            'aType' => $this->string()
        ]);

        $this->createTable('aCountry', [
            'id' => $this->primaryKey(),
            'aid' => $this->integer()->notNull(),
            'aName' => $this->string()->notNull(),
            'aType' => $this->string()
        ]);

        $this->createTable('aRegion', [
            'id' => $this->primaryKey(),
            'aid' => $this->integer()->notNull(),
            'aName' => $this->string()->notNull(),
            'aType' => $this->string()
        ]);

        $this->createTable('aBlog', [
            'id' => $this->primaryKey(),
            'aBlogHost' => $this->string()->notNull(),
            'aBlogHostId' => $this->integer()->notNull(),
            'aBlogHostType' => $this->integer()->notNull()
        ]);


    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('aCity');
        $this->dropTable('aCountry');
        $this->dropTable('aRegion');
        $this->dropTable('aBlog');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180319_051349_NewAdditionals cannot be reverted.\n";

        return false;
    }
    */
}
