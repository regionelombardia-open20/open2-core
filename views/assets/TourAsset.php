<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views\assets
 * @category   CategoryName
 */

namespace open20\amos\core\views\assets;

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
