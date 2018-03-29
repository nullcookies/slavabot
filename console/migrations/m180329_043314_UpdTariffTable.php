<?php

use yii\db\Migration;

/**
 * Class m180329_043314_UpdTariffTable
 */
class m180329_043314_UpdTariffTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropTable('slava_tariffs');

        $this->createTable('slava_tariffs', [
            'id' => $this->primaryKey(),
            'title' => $this->string(300),
            'description' => $this->string(5000),
            'cost' => $this->float(),
            'constraints' => $this->string(5000),
            'active' => $this->integer(),
            'displayed' => $this->integer(),
            'color' => $this->string(),
            'sort' => $this->integer()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('slava_tariffs');

        $this->createTable('slava_tariffs', [
            'id' => $this->primaryKey(),
            'title' => $this->string(300),
            'description' => $this->string(5000),
            'cost' => $this->float(),
            'constraints' => $this->string(),
            'active' => $this->integer()
        ]);

        $this->addColumn('slava_tariffs', 'color', $this->string());

        $this->addColumn('slava_tariffs', 'sort', $this->string());

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180329_043314_UpdTariffTable cannot be reverted.\n";

        return false;
    }
    */
}
