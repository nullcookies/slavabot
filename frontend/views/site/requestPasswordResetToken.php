<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\PasswordResetRequestForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = \Yii::t('main', 'Request password reset');
$this->params['breadcrumbs'][] = $this->title;
?>
<div id="login-box">
    <div id="login-box-holder">
        <div class="row">
            <div class="col-xs-12">
                <header id="login-header">
                    <div id="login-logo">
                        <img src="/cube/img/logo.png" alt=""/>
                        <span>Slavabot</span>
                    </div>
                </header>
                <div id="login-box-inner">
                    <?php $form = ActiveForm::begin(
                        [
                                'id' => 'request-password-reset-form'
                        ]
                    ); ?>

                    <?= $form->field($model, 'email',
                        [
                            'options'=>['class'=>'input-group'],
                            'template' => '<span class="input-group-addon"><i class="fa fa-user"></i></span>{input}'
                        ])
                        ->textInput(['autofocus' => true,'placeholder' =>  \Yii::t('main', 'Email')])
                    ?>
                    <div class="row">
                        <div class="col-xs-12">
                            <?= Html::submitButton(\Yii::t('main', 'Reset password'), ['class' => 'btn btn-success col-xs-12', 'name' => 'login-button_']) ?>
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