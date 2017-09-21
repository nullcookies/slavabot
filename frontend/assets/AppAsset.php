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

//    public $css = [
//        '/build/static/css/main.min.css',
//    ];
//    public $js = [
//        '/build/static/js/vendor.min.js',
//        '/build/static/js/main.min.js',
//        '/back.js'
//    ];
// /cube/
    public $css = [
        '/cube/components/bootstrap/dist/css/bootstrap.min.css',
        '/cube/components/font-awesome/css/font-awesome.css',
        '/cube/css/libs/nanoscroller.css',
        '/cube/css/compiled/theme_styles.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
    ];
}
