<?php

/* @var $this yii\web\View */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title =  \Yii::t('main', 'Settings');
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="col-lg-12">
    <div class="main-box clearfix">
        <div class="main-box-body clearfix">
            <div style="height:25px;"></div>
            <div class="tabs-wrapper">
                <ul class="nav nav-tabs">
                    <li class="<?= $active=='main' ? 'active' : ''?>""><a href="#tab-main" data-toggle="tab" aria-expanded="<?= $active=='main' ? 'true' : 'false'?>"><?=\Yii::t('main', 'User settings')?></a></li>
                    <li class="<?= $active=='password' ? 'active' : ''?>"><a href="#tab-help" data-toggle="tab" aria-expanded="<?= $active=='password' ? 'true' : 'false'?>"><?=\Yii::t('main', 'Change password')?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade <?= $active=='main' ? 'active in' : ''?>" id="tab-main">
                        <div class="main-box-body clearfix">
                            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                            <?= $form->field($modelUser, 'username',
                                [
                                    'options'=>['class'=>'form-group'],
                                    'template' => '{label}{input}'
                                ])
                                ->textInput(['placeholder' =>  \Yii::t('main', 'Username'), 'value' => \Yii::$app->user->identity->username])
                                ->label('Имя пользователя')
                            ?>
                            <div class="form-group">
                                <label for="exampleInputPassword1">Email</label>
                                <input class="form-control" type="text" placeholder="Email" disabled="" value="<?=Yii::$app->user->identity->email?>">
                            </div>
                            <?= $form->field($modelUser, 'phone',
                                [
                                    'options'=>['class'=>'form-group'],
                                    'template' => '{label}{input}'
                                ])
                                ->widget(\yii\widgets\MaskedInput::className(),['mask'=>'+7 (999) 999-99-99'])
                                ->textInput(['placeholder' => \Yii::t('main', 'Phone'), 'value' => \Yii::$app->user->identity->phone])
                                ->label('Телефон')

                            ?>
                            <?= Html::submitButton(\Yii::t('main', 'Save'), ['class' => 'btn btn-success', 'name' => 'login-button_']) ?>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                    <div class="tab-pane fade <?= $active=='password' ? 'active in' : ''?>" id="tab-help">
                        <div class="main-box-body clearfix">
                            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                            <?= $form->field($modelPassword, 'password',
                                [
                                    'options'=>['class'=>'form-group'],
                                    'template' => '{label}{input}'
                                ])
                                ->textInput(['type'=>'password','placeholder' =>  \Yii::t('main', 'Password')])
                                ->label('Текущий пароль')
                            ?>

                            <?= $form->field($modelPassword, 'new_password',
                                [
                                    'options'=>['class'=>'form-group'],
                                    'template' => '{label}{input}'
                                ])
                                ->textInput(['type'=>'password','placeholder' =>  \Yii::t('main', 'Password')])
                                ->label('Новый пароль')
                            ?>

                            <?= $form->field($modelPassword, 'new_password_repeat',
                                [
                                    'options'=>['class'=>'form-group'],
                                    'template' => '{label}{input}'
                                ])
                                ->textInput(['type'=>'password','placeholder' =>  \Yii::t('main', 'Password')])
                                ->label('Повторите новый пароль')
                            ?>

                            <?= Html::submitButton(\Yii::t('main', 'Save'), ['class' => 'btn btn-success', 'name' => 'login-button_']) ?>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
