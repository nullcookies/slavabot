<?php

use yii\db\Migration;

/**
 * Class m171227_072855_TariffsColor
 */
class m171227_072855_TariffsColor extends Migration
{
    public function up()
    {
        $this->addColumn('slava_tariffs', 'color', $this->string());

    }

    public function down()
    {
        $this->dropColumn('slava_tariffs', 'color');

    }
}
