<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;
use yii\widgets\Menu;


AppAsset::register($this);
?>
<?php $this->beginPage() ?>

<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>"  ng-app="cubeWebApp">
<head>
    <meta  charset="<?= Yii::$app->charset ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <?= Html::csrfMetaTags() ?>
    <title ng-bind="title"><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400' rel='stylesheet' type='text/css'>

    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
</head>
<?php $this->beginBody() ?>

<body class=" fixed-header pace-done">
<div id="theme-wrapper">
    <header class="navbar" id="header-navbar" ng-controller="header">
        <div class="container">
            <a href="#/dasboard" id="logo" class="navbar-brand">
                <img src="/cube/img/logo.png" alt="" class="normal-logo logo-white"/>
                <img src="/cube/img/logo-black.png" alt="" class="normal-logo logo-black"/>
                <img src="/cube/img/logo-small.png" alt="" class="small-logo hidden-xs hidden-sm hidden"/>
                <span>BotSales</span>
            </a>

            <div class="clearfix">
                <button class="navbar-toggle" data-target=".navbar-ex1-collapse" data-toggle="collapse" type="button">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="fa fa-bars"></span>
                </button>

                <div class="nav-no-collapse navbar-left pull-left hidden-sm hidden-xs">
                    <ul class="nav navbar-nav pull-left">
                        <li>
                            <a class="btn" id="make-small-nav">
                                <i class="fa fa-bars"></i>
                            </a>
                        </li>
<!--                        <li class="dropdown hidden-xs">-->
<!--                            <a class="btn dropdown-toggle" data-toggle="dropdown">-->
<!--                                <i class="fa fa-bell"></i>-->
<!--                                <span class="count">8</span>-->
<!--                            </a>-->
<!--                            <ul class="dropdown-menu notifications-list">-->
<!--                                <li class="pointer">-->
<!--                                    <div class="pointer-inner">-->
<!--                                        <div class="arrow"></div>-->
<!--                                    </div>-->
<!--                                </li>-->
<!--                                <li class="item-header">You have 6 new notifications</li>-->
<!--                                <li class="item">-->
<!--                                    <a href="#">-->
<!--                                        <i class="fa fa-comment"></i>-->
<!--                                        <span class="content">New comment on ‘Awesome P...</span>-->
<!--                                        <span class="time"><i class="fa fa-clock-o"></i>13 min.</span>-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                                <li class="item">-->
<!--                                    <a href="#">-->
<!--                                        <i class="fa fa-plus"></i>-->
<!--                                        <span class="content">New user registration</span>-->
<!--                                        <span class="time"><i class="fa fa-clock-o"></i>13 min.</span>-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                                <li class="item">-->
<!--                                    <a href="#">-->
<!--                                        <i class="fa fa-envelope"></i>-->
<!--                                        <span class="content">New Message from George</span>-->
<!--                                        <span class="time"><i class="fa fa-clock-o"></i>13 min.</span>-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                                <li class="item">-->
<!--                                    <a href="#">-->
<!--                                        <i class="fa fa-shopping-cart"></i>-->
<!--                                        <span class="content">New purchase</span>-->
<!--                                        <span class="time"><i class="fa fa-clock-o"></i>13 min.</span>-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                                <li class="item">-->
<!--                                    <a href="#">-->
<!--                                        <i class="fa fa-eye"></i>-->
<!--                                        <span class="content">New order</span>-->
<!--                                        <span class="time"><i class="fa fa-clock-o"></i>13 min.</span>-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                                <li class="item-footer">-->
<!--                                    <a href="#">-->
<!--                                        View all notifications-->
<!--                                    </a>-->
<!--                                </li>-->
<!--                            </ul>-->
<!--                        </li>-->
                    </ul>
                </div>


                <div class="nav-no-collapse pull-right" id="header-nav">
                    <ul class="nav navbar-nav pull-right">
                        <li class="dropdown profile-dropdown">
                            <span>
                                <span class="hidden-xs"><?=Yii::$app->user->identity->username?></span>
                            </span>
                        </li>
                        <li class="hidden-xxs">
                            <?= Html::a('<i class="fa fa-cog"></i>'.\Yii::t('main', 'Settings'), ['#/pages/config'], ['class' => 'btn']) ?>
                        </li>
                        <li class="hidden-xxs">
                            <?= Html::a('<i class="fa fa-life-ring"></i>'.\Yii::t('main', 'Help'), ['#/pages/help'], ['class' => 'btn']) ?>
                        </li>
                        <li class="hidden-xxs">
                            <?= Html::a('<i class="fa fa-power-off"></i>'.\Yii::t('main', 'Logout'), ['site/logout'], ['class' => 'btn']) ?>
                        </li>
                    </ul>
                </div>
                <div class="nav-no-collapse pull-right" id="header-nav">
<!--                    <ul class="nav navbar-nav pull-right">-->
<!--                        <li class="dropdown profile-dropdown">-->
<!--                            <span class="hidden-xs">Баланс: 2 000</span>-->
<!--                            <button type="button" class="btn btn-success">Пополнить</button>-->
<!--                        </li>-->
<!--                    </ul>-->
                </div>
            </div>
        </div>
    </header>
    <div id="page-wrapper" class="container">
        <div class="row">
            <div id="nav-col" ng-controller="menu">
                <section id="col-left" class="col-left-nano">
                    <div id="col-left-inner" class="col-left-nano-content">
                        <div class="collapse navbar-collapse navbar-ex1-collapse" id="sidebar-nav" bs-navbar>
                            <ul class="nav nav-pills nav-stacked">
                                <li data-match-route="/potential*" class="header-nav__item">
                                    <a href="#/potential">
                                        <i class="fa fa-list"></i>
                                        <span style="font-size: 0.775em;">Потенциальные клиенты</span>
                                    </a>
                                    <ul class="submenu">
                                        <li ng-repeat="menu in potentialSubMenu">
                                            <a data-match-route="/potential/{{menu.id}}" href="#/potential/{{menu.id}}">
                                                {{menu.name}}
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="header-nav__item" data-match-route="/pages/contacts"><a href="#/pages/contacts"><i class="fa fa-folder-open-o"></i><span>Купленные контакты</span></a></li>
<!--                                <li class="header-nav__item" data-match-route="/pages/config"><a href="#/pages/config"><i class="fa fa-sliders"></i><span>Настройки</span></a></li>-->
<!--                                <li class="header-nav__item"><a href="#/pages/help"><i class="fa fa-life-ring"></i><span>Помощь</span></a></li>-->
                            </ul>
                        </div>
                    </div>
                </section>
                <div id="nav-col-submenu"></div>
            </div>
            <div id="content-wrapper">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="slide-main-container">
                            <div ng-view autoscroll="true" class="slide-main-animation"></div>
                        </div>

                    </div>
                </div>

                <div ng-include='"views/common/footer.html"'></div>
            </div>
        </div>
    </div>
</div>

<div id="config-tool" class="closed">
    <a id="config-tool-cog">
        <i class="fa fa-cog"></i>
    </a>

    <div id="config-tool-options">
        <h4>Layout Options</h4>
        <ul>
            <li>
                <div class="checkbox-nice">
                    <input type="checkbox" id="config-fixed-header" />
                    <label for="config-fixed-header">
                        Fixed Header
                    </label>
                </div>
            </li>
            <li>
                <div class="checkbox-nice">
                    <input type="checkbox" id="config-fixed-sidebar" />
                    <label for="config-fixed-sidebar">
                        Fixed Left Menu
                    </label>
                </div>
            </li>
            <li>
                <div class="checkbox-nice">
                    <input type="checkbox" id="config-fixed-footer" />
                    <label for="config-fixed-footer">
                        Fixed Footer
                    </label>
                </div>
            </li>
            <li>
                <div class="checkbox-nice">
                    <input type="checkbox" id="config-boxed-layout" />
                    <label for="config-boxed-layout">
                        Boxed Layout
                    </label>
                </div>
            </li>
            <li>
                <div class="checkbox-nice">
                    <input type="checkbox" id="config-rtl-layout" />
                    <label for="config-rtl-layout">
                        Right-to-Left
                    </label>
                </div>
            </li>
        </ul>
        <br/>
        <h4>Skin Color</h4>
        <ul id="skin-colors" class="clearfix">
            <li>
                <a class="skin-changer" data-skin="" data-toggle="tooltip" title="Default" style="background-color: #34495e;">
                </a>
            </li>
            <li>
                <a class="skin-changer" data-skin="theme-white" data-toggle="tooltip" title="White/Green" style="background-color: #2ecc71;">
                </a>
            </li>
            <li>
                <a class="skin-changer blue-gradient" data-skin="theme-blue-gradient" data-toggle="tooltip" title="Gradient">
                </a>
            </li>
            <li>
                <a class="skin-changer" data-skin="theme-turquoise" data-toggle="tooltip" title="Green Sea" style="background-color: #1abc9c;">
                </a>
            </li>
            <li>
                <a class="skin-changer" data-skin="theme-amethyst" data-toggle="tooltip" title="Amethyst" style="background-color: #9b59b6;">
                </a>
            </li>
            <li>
                <a class="skin-changer" data-skin="theme-blue" data-toggle="tooltip" title="Blue" style="background-color: #2980b9;">
                </a>
            </li>
            <li>
                <a class="skin-changer" data-skin="theme-red" data-toggle="tooltip" title="Red" style="background-color: #e74c3c;">
                </a>
            </li>
            <li>
                <a class="skin-changer" data-skin="theme-whbl" data-toggle="tooltip" title="White/Blue" style="background-color: #3498db;">
                </a>
            </li>
        </ul>
    </div>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>