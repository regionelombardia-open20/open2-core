<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\layout
 * @category   CategoryName
 */

namespace open20\amos\core\views\assets;

use yii\web\AssetBundle;

/**
 * Class AppAsset
 * @package open20\amos\layout\assets
 */

class IsotopeAsset extends AssetBundle {

    public $sourcePath = '@vendor/open20/amos-core/views/assets/web';
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
