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
    ];
    public $js = [
        '/cube/js/demo-skin-changer.js',
        //'/cube/components/jquery/dist/jquery.min.js',
        '/cube/components/bootstrap/dist/js/bootstrap.js',
        '/cube/components/nanoscroller/bin/javascripts/jquery.nanoscroller.min.js',
        '/cube/js/demo.js',
        '/cube/js/scripts.js',
        '/cube/components/PACE/pace.min.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        //'yii\bootstrap\BootstrapAsset',
    ];
}
