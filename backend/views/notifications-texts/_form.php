<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\NotificationsTexts */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="notifications-texts-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'type')->dropDownlist([
        '0' => 'Нет данных',
        '1' => 'Мотивационные',
    ])->label('Тип уведомления') ?>

    <?= $form->field($model, 'text')->textInput(['maxlength' => true])->label('Текст уведомления') ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Обновить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
