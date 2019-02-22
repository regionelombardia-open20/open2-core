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

class IE8Assets extends AssetBundle {

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        //DA SOSTITUIRE CON FILE LESS UNA VOLTA INSERITO IL COMPILATORE
        'css/widgetsIE8.css'
    ];
    public $cssOptions = ['condition' => 'lt IE9'];
    public $js = [
        '/js/html5shiv.js',
        '/js/respond.js',
        '/js/svg4everybody.legacy.js'
    ];
    public $jsOptions = ['condition' => 'lt IE9'];

}
