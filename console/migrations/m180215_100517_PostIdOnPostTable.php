<?php

use yii\db\Migration;

/**
 * Class m180215_100517_PostIdOnPostTable
 */
class m180215_100517_PostIdOnPostTable extends Migration
{
    public function up()
    {
        $this->addColumn('table_posts', 'result_post_id', $this->string());

    }

    public function down()
    {
        $this->dropColumn('table_posts', 'result_post_id');

    }
}
