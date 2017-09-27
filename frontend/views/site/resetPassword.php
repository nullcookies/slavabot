<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\ResetPasswordForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Reset password';
$this->params['breadcrumbs'][] = $this->title;
?>



<div id="login-box">
    <div id="login-box-holder">
        <div class="row">
            <div class="col-xs-12">
                <header id="login-header">
                    <div id="login-logo">
                        <img src="/cube/img/logo.png" alt=""/>
                        <span>BotSales</span>
                    </div>
                </header>
                <div id="login-box-inner">
                    <?php $form = ActiveForm::begin(
                        [
                            'id' => 'reset-password-form'
                        ]
                    ); ?>

                    <?= $form->field($model, 'password',
                        [
                            'options'=>['class'=>'input-group'],
                            'template' => '<span class="input-group-addon"><i class="fa fa-user"></i></span>{input}'
                        ])
                        ->textInput(['type'=>'password','autofocus' => true,'placeholder' =>  \Yii::t('main', 'Password')])
                    ?>

                    <div class="row">
                        <div class="col-xs-12">
                            <?= Html::submitButton(\Yii::t('main', 'Set password'), ['class' => 'btn btn-success col-xs-12', 'name' => 'login-button_']) ?>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>

    <div id="login-box-footer">
        <div class="row">
            <div class="col-xs-12">
                <?=\Yii::t('main', 'Do not have an account?')?>
                <?= Html::a(\Yii::t('main', 'Register now'), ['site/signup']) ?>
            </div>
        </div>
    </div>
</div>