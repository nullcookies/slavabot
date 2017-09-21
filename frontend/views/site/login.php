<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = \Yii::t('main', 'Login');
$this->params['breadcrumbs'][] = $this->title;
?>



<div id="login-box-inner">
    <?php $form = ActiveForm::begin(
            [
                'id' => 'login-form',
            ]
    ); ?>

    <?= $form->field($model, 'username',
        [
            'options'=>['class'=>'input-group'],
            'template' => '<span class="input-group-addon"><i class="fa fa-user"></i></span>{input}'
        ])
        ->textInput(['autofocus' => true,'placeholder' =>  \Yii::t('main', 'Username')])
    ?>

    <?= $form->field($model, 'password',
        [
            'options'=>['class'=>'input-group'],
            'template' => '<span class="input-group-addon"><i class="fa fa-user"></i></span>{input}'
        ])
        ->passwordInput(['autofocus' => true,'placeholder' =>  \Yii::t('main', 'Password')])
    ?>
    <div id="remember-me-wrapper">
        <div class="row">
            <div class="col-xs-6">

            <?= $form->field($model, 'rememberMe',
                [
                    'options'=>['class'=>'checkbox-nice'],

                ])->checkbox(['template' => '{input}{label}',])->label(\Yii::t('main', 'Remember me')) ?>

            </div>
            <?= Html::a(\Yii::t('main', 'Forgot password?'), ['site/request-password-reset'], ['id'=>'login-forget-link', 'class'=>'col-xs-6']) ?>

        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <?= Html::submitButton(\Yii::t('main', 'Login'), ['class' => 'btn btn-success col-xs-12', 'name' => 'login-button_']) ?>
        </div>
    </div>
        <div class="row">
            <div class="col-xs-12">
                <p class="social-text"><?=\Yii::t('main', 'Or login with')?></p>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-6">
                <button type="submit" class="btn btn-primary col-xs-12 btn-facebook">
                    <i class="fa fa-facebook"></i> facebook
                </button>
            </div>
            <div class="col-xs-12 col-sm-6">
                <button type="submit" class="btn btn-primary col-xs-12 btn-twitter">
                    <i class="fa fa-twitter"></i> Twitter
                </button>
            </div>
        </div>
    <?php ActiveForm::end(); ?>
</div>
