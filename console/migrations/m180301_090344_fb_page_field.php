<?php

use yii\db\Migration;

/**
 * Class m180301_090344_fb_page_field
 */
class m180301_090344_fb_page_field extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('social', 'fb_page', $this->bigInteger()->null()->after('data'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('social', 'fb_page');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180301_090344_fb_page_field cannot be reverted.\n";

        return false;
    }
    */
}
