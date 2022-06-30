<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms\editors\assets
 * @category   CategoryName
 */

namespace open20\amos\core\forms\editors\assets;

use yii\web\AssetBundle;

class EditorsAsset extends AssetBundle
{
    public $sourcePath = '@vendor/open20/amos-core/forms/editors/assets/web';

    public $css = [
    ];
    public $js = [
        'js/cartewidget.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}