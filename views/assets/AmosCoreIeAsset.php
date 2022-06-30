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

class AmosCoreIeAsset extends AssetBundle
{
    public $sourcePath = '@vendor/open20/amos-core/views/assets/web';

    public $css = [
        //DA SOSTITUIRE CON FILE LESS UNA VOLTA INSERITO IL COMPILATORE
        //'css/less/tables_ie.less',
    ];
    public $cssOptions = [
        'condition' => 'IE',
    ];
    public $js = [       
    ];
    public $depends = [
        'open20\amos\core\views\assets\AmosCoreAsset',
    ];
}
