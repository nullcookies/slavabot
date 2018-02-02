<?php

use yii\db\Migration;

/**
 * Class m180131_072810_PayTotalPrice
 */
class m180131_072810_PayTotalPrice extends Migration
{
    public function up()
    {
        $this->addColumn('slava_payment', 'totalPrice', $this->float());

    }

    public function down()
    {
        $this->dropColumn('slava_payment', 'totalPrice');

    }
}
