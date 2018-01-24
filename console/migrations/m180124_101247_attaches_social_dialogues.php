<?php

use yii\db\Migration;

/**
 * Class m180124_101247_attaches_social_dialogues
 */
class m180124_101247_attaches_social_dialogues extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('social_dialogues', 'attaches', $this->text()->null()->after('message'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('social_dialogues', 'attaches');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180124_101247_attaches_social_dialogues cannot be reverted.\n";

        return false;
    }
    */
}
