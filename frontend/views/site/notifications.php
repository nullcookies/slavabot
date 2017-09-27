<?php

/* @var $this yii\web\View */

$this->title =  \Yii::t('main', 'Notifications');
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="col-lg-12">
    <div class="main-box">
        <header class="main-box-header clearfix">
            <h2>Отображать уведомления:</h2>
        </header>
        <div class="main-box-body clearfix">
            <div class="form-group">
                <div class="radio">
                    <input type="radio" name="optionsRadios" id="optionsRadios1" value="option1" checked="">
                    <label for="optionsRadios1">
                        По мере появления
                    </label>
                </div>
                <div class="radio">
                    <input type="radio" name="optionsRadios" id="optionsRadios2" value="option2">
                    <label for="optionsRadios2">
                        Раз в час
                    </label>
                </div>
            </div>
            <div class="form-group">
                <button type="button" class="btn btn-success">Сохранить</button>
            </div>
        </div>
    </div>
</div>