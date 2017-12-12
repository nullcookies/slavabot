<?php

use yii\db\Migration;

/**
 * Class m171212_050206_RenameUserTable
 */
class m171212_050206_RenameUserTable extends Migration
{
    public function up()
    {
        $this->renameTable('user', 'salesbot_user');

    }

    public function down()
    {
        $this->renameTable('salesbot_user', 'user');

    }
}
