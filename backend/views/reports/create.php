<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Reports */

$this->title = 'Новая выгрузка';
$this->params['breadcrumbs'][] = ['label' => 'Выгрузки', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="reports-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
