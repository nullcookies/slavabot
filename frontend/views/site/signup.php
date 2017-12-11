<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\SignupForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = \Yii::t('main', 'Signup');
$this->params['breadcrumbs'][] = $this->title;
$success = Yii::$app->request->get('success');
?>

<div id="login-box">
    <div class="row">
        <div class="col-xs-12">
            <header id="login-header">
                <div id="login-logo">
                    <img src="/cube/img/logo.png" alt=""/>
                    <span>Slavabot</span>
                </div>
            </header>
            <? if(!$success){?>
            <div id="login-box-inner" >
                <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

                    <?= $form->field($model, 'username',
                        [
                            'options'=>['class'=>'input-group'],
                            'template' => '<span class="input-group-addon"><i class="fa fa-user"></i></span>{input}'
                        ])
                        ->textInput(['placeholder' =>  \Yii::t('main', 'Username')])
                    ?>
                    <?= $form->field($model, 'email',
                        [
                            'options'=>['class'=>'input-group'],
                            'template' => '<span class="input-group-addon"><i class="fa fa-envelope"></i></span>{input}'
                        ])
                        ->textInput(['placeholder' =>  \Yii::t('main', 'Email address')])
                    ?>
                    <?= $form->field($model, 'phone',
                        [
                            'options'=>['class'=>'input-group'],
                            'template' => '<span class="input-group-addon"><i class="fa fa-phone"></i></span>{input}'
                        ])
                        ->widget(\yii\widgets\MaskedInput::className(),['mask'=>'+7 (999) 999-99-99'])
                        ->textInput(['placeholder' => \Yii::t('main', 'Phone')])
                        ->label(false)

                    ?>
                    <div id="remember-me-wrapper">
                        <div class="row">
                            <div class="col-xs-12">
                                <?= $form->field($model, 'terms_cond', ['options'=>['class'=>'checkbox-nice']])
                                    ->checkbox(['template' => '{input}{label}'])
                                    ->label(\Yii::t('main', 'I accept terms and conditions'), [ 'class'=> 'control-label']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12">
                            <?= Html::submitButton(\Yii::t('main', 'Register'), ['class' => 'btn btn-success col-xs-12', 'name' => 'login-button_']) ?>
                        </div>
                    </div>
                <?php ActiveForm::end(); ?>
            </div>
            <? } elseif($success){?>
                <div id="login-box-inner" style="height: 190px;">
                    <p>Данные для входа отправлены на почту!</p>
                    <p>Теперь вы можете авторизоваться.</p>

                    <a href="/site/login" class="btn btn-success col-xs-12">Авторизоваться</a>
                </div>
            <? }?>

        </div>
    </div>
</div>
