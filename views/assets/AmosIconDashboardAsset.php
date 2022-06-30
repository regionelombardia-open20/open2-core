<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views\assets
 * @category   CategoryName
 */

namespace open20\amos\core\views\assets;

use yii\web\AssetBundle;

class AmosIconDashboardAsset extends AssetBundle
{
    public $sourcePath = '@vendor/open20/amos-core/views/assets/web';
    public $baseUrl = '@web';

    public $css = [
        'css/fonts/icon-dashboard/style.css',
    ];
    
    public $js = [
    ];
    
    public $depends = [
    ];
}
