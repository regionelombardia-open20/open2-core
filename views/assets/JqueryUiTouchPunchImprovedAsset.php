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

class JqueryUiTouchPunchImprovedAsset extends AssetBundle {

    public $sourcePath = '@bower/jquery-ui-touch-punch-improved';
    public $js = [
        'jquery.ui.touch-punch-improved.js',
    ];
    public $depends = [
        'yii\jui\JuiAsset'
    ];
}
