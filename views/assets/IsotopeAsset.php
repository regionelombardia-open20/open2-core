<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\layout
 * @category   CategoryName
 */

namespace lispa\amos\core\views\assets;

use yii\web\AssetBundle;

/**
 * Class AppAsset
 * @package lispa\amos\layout\assets
 */

class IsotopeAsset extends AssetBundle {

    public $sourcePath = '@vendor/lispa/amos-core/views/assets/web';
    public $css = [
    ];
    public $js = [
    'js/isotope.pkgd.min.js',
    'js/packery-mode.pkgd.js',

    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];

}
