<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\billing\Tariffs */

$this->title = 'Создать новый тариф';
$this->params['breadcrumbs'][] = ['label' => 'Тарифы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tariffs-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
