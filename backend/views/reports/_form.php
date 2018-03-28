<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Reports */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="reports-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'active')->checkbox(['value' => 1, 'label' => 'Активна', 'uncheck' => '0']) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('Название выгрузки') ?>

    <?= $form->field($model, 'mlg_id')->textInput()->label('ID отчета sm.mlg.ru') ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Обновить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
