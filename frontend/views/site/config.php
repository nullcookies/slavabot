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
                    <li ng-class="{'active' : mainPlane}"><a ng-click="setMain()"><?=\Yii::t('main', 'User settings')?></a></li>
                    <li ng-class="{'active' : passwordPlane}"><a ng-click="setPassword()"><?=\Yii::t('main', 'Change password')?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="main-box-body clearfix">
                        <div class="alert alert-success fade in" ng-show="userSuccess">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <i class="fa fa-check-circle fa-fw fa-lg"></i>
                            Данные успешно сохранены.
                        </div>
                        <div class="alert alert-danger fade in" ng-show="userError">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <i class="fa fa-times-circle fa-fw fa-lg"></i>
                            <strong>Ошибка!</strong> Попробуйте позже.
                        </div>
                    </div>
                    <div ng-class="{'active in' : mainPlane}" class="tab-pane fade" id="tab-main">
                        <div class="main-box-body clearfix">
                            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                            <?= $form->field($modelUser, 'username',
                                [
                                    'options'=>['class'=>'form-group'],
                                    'template' => '{label}{input}'
                                ])
                                ->textInput(['ng-model'=> 'username', 'placeholder' =>  \Yii::t('main', 'Username')])
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
                                ->textInput(['ng-model'=> 'phone', 'placeholder' => \Yii::t('main', 'Phone')])
                                ->label('Телефон')

                            ?>
                            <?= Html::button(\Yii::t('main', 'Save'), ['class' => 'btn btn-success', 'ng-click' => 'saveUser()', 'name' => 'login-button_']) ?>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                    <div ng-class="{'active in' : passwordPlane}" class="tab-pane fade" id="tab-help">
                        <div class="main-box-body clearfix">
                            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                            <?= $form->field($modelPassword, 'password',
                                [
                                    'options'=>['class'=>'form-group', 'ng-class'=>"{'has-error' : passwordError}"],
                                    'template' => '{label}{input}'
                                ])
                                ->textInput(['ng-model'=>'password', 'type'=>'password','placeholder' =>  \Yii::t('main', 'Password')])
                                ->label('Текущий пароль')
                            ?>

                            <?= $form->field($modelPassword, 'new_password',
                                [
                                    'options'=>['class'=>'form-group', 'ng-class'=>"{'has-error' : new_passwordError}"],
                                    'template' => '{label}{input}'
                                ])
                                ->textInput(['ng-model'=>'new_password', 'type'=>'password','placeholder' =>  \Yii::t('main', 'Password')])
                                ->label('Новый пароль')
                            ?>

                            <?= $form->field($modelPassword, 'new_password_repeat',
                                [
                                    'options'=>['ng-class'=>"{'has-error' : new_password_repeatError}", 'class'=>'form-group'],
                                    'template' => '{label}{input}'
                                ])
                                ->textInput(['ng-model'=>'new_password_repeat', 'type'=>'password','placeholder' =>  \Yii::t('main', 'Password')])
                                ->label('Повторите новый пароль')
                            ?>

                            <?= Html::button(\Yii::t('main', 'Save'), ['ng-click'=>'savePassword()', 'class' => 'btn btn-success', 'name' => 'login-button_']) ?>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
