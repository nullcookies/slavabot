<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        '/cube/components/bootstrap/dist/css/bootstrap.min.css',
        '/cube/components/font-awesome/css/font-awesome.css',
        '/cube/css/libs/nanoscroller.css',
        '/cube/css/compiled/theme_styles.css',
        '/cube/angularjs/css/back.css',
    ];
    public $js = [
        '/cube/js/demo-skin-changer.js',
        //'/cube/components/jquery/dist/jquery.min.js',
        '/cube/components/bootstrap/dist/js/bootstrap.js',
        '/cube/components/nanoscroller/bin/javascripts/jquery.nanoscroller.min.js',
        '/cube/js/demo.js',
        '/cube/js/scripts.js',
        '/cube/components/PACE/pace.min.js',
        '/cube/angularjs/js/angular.min.js',
        '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js',
        '//cdnjs.cloudflare.com/ajax/libs/angular-moment/0.9.0/angular-moment.min.js',
        '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/locales.js',
        '/cube/angularjs/js/angular-route.min.js',
        '/cube/angularjs/js/angular-animate.js',
        '/cube/angularjs/js/loading-bar.js',
        '/cube/angularjs/js/angular.easypiechart.min.js',
        '/cube/angularjs/app/app.js',
        '/cube/angularjs/app/directives.js',
        '/cube/angularjs/app/controllers.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
    ];
}

