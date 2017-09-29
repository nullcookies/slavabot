<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
    <div class="col-lg-12">
        <div class="main-box clearfix">
            <header class="main-box-header clearfix">
                <h2>Отчетная страница</h2>
            </header>

            <div class="main-box-body clearfix">
                <div class="jumbotron">
                    <h1>Принято WebHooks: <?=$webhooks?></h1>
                </div>

                <div class="body-content">
                    <h1>Наполнение справочников:</h1>
                    <div class="row">
                        <div class="col-lg-3">
                            <h2>Локации:</h2>
                            <? foreach($location as $item){ ?>
                                <p>[<?=$item->mlg_id?>] <?=$item->name?></p>
                            <? } ?>
                        </div>
                        <div class="col-lg-3">
                            <h2>Категории:</h2>
                            <? foreach($category as $item){ ?>
                                <p>[<?=$item->mlg_id?>] <?=$item->name?></p>
                            <? } ?>
                        </div>
                        <div class="col-lg-3">
                            <h2>Приоритет:</h2>
                            <? foreach($priority as $item){ ?>
                                <p>[<?=$item->mlg_id?>] <?=$item->name?></p>
                            <? } ?>
                        </div>
                        <div class="col-lg-3">
                            <h2>Темы:</h2>
                            <? foreach($theme as $item){ ?>
                                <p>[<?=$item->mlg_id?>] <?=$item->name?></p>
                            <? } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

