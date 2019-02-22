<?php
/**
* Lombardia Informatica S.p.A.
* OPEN 2.0
*
*
* @package    openinnovation\landing
* @category   CategoryName
*/

namespace lispa\amos\core\forms\editors\assets;

use yii\web\AssetBundle;

/**
* Main frontend application asset bundle.
* @package frontend\assets
*/
class ExportMenuAsset extends AssetBundle
{
    public $sourcePath = '@vendor/lispa/amos-core/forms/editors/assets/web';

    public $css = [
        'css/kv-export-data.css',
    ];
    public $js = [
        'js/kv-export-data.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}

?>