<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\billing\Tariffs */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tariffs-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cost')->textInput()->label('Стоимость') ?>

    <?= $form->field($model, 'constraints[filters]')->Input('number',['maxlength' => true])->label('Количество фильтров'); ?>

    <?=
        $form->field($model, 'displayed')->dropDownList([
            1 => 'Общедоступный',
            0 => 'Корпоративный'
        ]);
    ?>

    <?=
         $form->field($model, 'color')->dropDownList([
                '' => 'По умолчанию',
                'green-bg' => 'Зеленый',
                'yellow-bg' => 'Желтый',
                'purple-bg'=>'Пурпурный'
         ])->label('Фон при выводе');
    ?>

    <?= $form->field($model, 'sort')->textInput()->label('Сортировка (по возрастанию)') ?>

    <?= $form->field($model, 'active')->checkbox(['value' => 1, 'uncheck' => '0']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
