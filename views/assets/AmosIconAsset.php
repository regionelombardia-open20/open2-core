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

class AmosIconAsset extends AssetBundle
{
    public $sourcePath = '@vendor/lispa/amos-core/views/assets/web';
    public $baseUrl = '@web';

    public $css = [
        'css/fonts/icon-font/styles.css',
    ];
    
    public $js = [
    ];
    
    public $depends = [
    ];
}
