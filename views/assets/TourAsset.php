<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views\assets
 * @category   CategoryName
 */

namespace lispa\amos\core\views\assets;

use yii\web\AssetBundle;

class TourAsset extends AssetBundle {

    public $sourcePath = '@bower/bootstrap-tour';
    public $js = [
        'build/js/bootstrap-tour.min.js',
    ];
    public $css = [
        'build/css/bootstrap-tour.min.css',
    ];
    public $depends = [
        'yii\bootstrap\BootstrapPluginAsset',
    ];

}
