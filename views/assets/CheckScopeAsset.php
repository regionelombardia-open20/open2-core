<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    Open20Package
 * @category   CategoryName
 */



namespace open20\amos\core\views\assets;

use yii\web\AssetBundle;

class CheckScopeAsset extends AssetBundle{
    
   
    
    public $sourcePath = '@vendor/open20/amos-core/views/assets/web';
    
    public $css = [
    ];
    public $js = [
        'js/checkscope.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
    
    
    public function init() {
        parent::init();
    }
}
