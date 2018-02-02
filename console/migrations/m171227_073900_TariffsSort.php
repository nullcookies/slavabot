<?php

use yii\db\Migration;

/**
 * Class m171227_073900_TariffsSort
 */
class m171227_073900_TariffsSort extends Migration
{
    public function up()
    {
        $this->addColumn('slava_tariffs', 'sort', $this->string());

    }

    public function down()
    {
        $this->dropColumn('slava_tariffs', 'sort');

    }
}
