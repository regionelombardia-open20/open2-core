<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    Open20Package
 * @category   CategoryName
 */
/**
* Lombardia Informatica S.p.A.
* OPEN 2.0
*
*
* @package    openinnovation\landing
* @category   CategoryName
* @author     Lombardia Informatica S.p.A.
*/

namespace open20\amos\core\forms\editors\assets;

use yii\web\AssetBundle;

/**
* Main frontend application asset bundle.
* @package frontend\assets
*/
class ExportMenuAsset extends AssetBundle
{
    public $sourcePath = '@vendor/open20/amos-core/forms/editors/assets/web';

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