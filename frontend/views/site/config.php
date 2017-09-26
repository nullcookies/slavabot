<?php

/* @var $this yii\web\View */

$this->title =  \Yii::t('main', 'Settings');
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="col-lg-12">
    <div class="main-box clearfix">
        <div class="main-box-body clearfix">
            <div style="height:25px;"></div>
            <div class="tabs-wrapper">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#tab-main" data-toggle="tab" aria-expanded="true"><?=\Yii::t('main', 'User settings')?></a></li>
                    <li class=""><a href="#tab-help" data-toggle="tab" aria-expanded="false"><?=\Yii::t('main', 'Change password')?></a></li>
                    <li class=""><a href="#tab-notifications" data-toggle="tab" aria-expanded="false"><?=\Yii::t('main', 'Notifications')?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade active in" id="tab-main">
                        <div class="main-box-body clearfix">
                            <form role="form">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Имя пользователя</label>
                                    <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Имя пользователя" value="<?=Yii::$app->user->identity->username?>">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Email</label>
                                    <input class="form-control" id="exampleInputFile" type="text" placeholder="Email" disabled="" value="<?=Yii::$app->user->identity->email?>">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Телефон</label>
                                    <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Телефон" value="<?=Yii::$app->user->identity->phone?>">
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-success">Сохранить</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab-help">
                        <div class="main-box-body clearfix">
                            <form role="form">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Старый пароль</label>
                                    <input type="password" class="form-control" id="exampleInputEmail1" placeholder="Старый пароль">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Новый пароль</label>
                                    <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Новый пароль">
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputPassword1">Повторите новый пароль</label>
                                    <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Повторите новый пароль">
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-success">Сохранить</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="tab-pane fade " id="tab-notifications">
                        <div class="main-box-body clearfix">
                            <form role="form">
                                <div class="form-group">
                                    <label>Настройка уведомлений</label>
                                    <div class="checkbox-nice">
                                        <input type="checkbox" id="checkbox-1" checked="checked">
                                        <label for="checkbox-1">
                                            По мере появления
                                        </label>
                                    </div>
                                    <div class="checkbox-nice">
                                        <input type="checkbox" id="checkbox-2">
                                        <label for="checkbox-2">
                                            Раз в час
                                        </label>
                                    </div>
                                    <div class="form-group">
                                        <button type="button" class="btn btn-success">Сохранить</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
