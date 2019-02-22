<?php



namespace lispa\amos\core\views\assets;

use yii\web\AssetBundle;

class CheckScopeAsset extends AssetBundle{
    
   
    
    public $sourcePath = '@vendor/lispa/amos-core/views/assets/web';
    
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
