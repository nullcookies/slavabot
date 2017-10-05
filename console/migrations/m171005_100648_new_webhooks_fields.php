<?php

use yii\db\Migration;

class m171005_100648_new_webhooks_fields extends Migration
{
    public function safeUp()
    {
        $this->addColumn('webhooks', 'author_image_url', $this->string()->null());
        $this->addColumn('webhooks', 'author_url', $this->string()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('webhooks', 'author_image_url');
        $this->dropColumn('webhooks', 'author_url');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171005_100648_new_webhooks_fields cannot be reverted.\n";

        return false;
    }
    */
}
