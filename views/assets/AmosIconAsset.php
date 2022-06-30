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

class AmosIconAsset extends AssetBundle
{
    public $sourcePath = '@vendor/open20/amos-core/views/assets/web';
    public $baseUrl = '@web';

    public $css = [
        'css/fonts/icon-font/styles.css',
    ];
    
    public $js = [
    ];
    
    public $depends = [
    ];
}
