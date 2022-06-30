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

class AmosCoreAsset extends AssetBundle
{
    public $sourcePath = '@vendor/open20/amos-core/views/assets/web';
    public $baseUrl = '@web';

    public $css = [
        //TODO AGGIUNGERE FILE LESS IE
        'css/less/core.less',
    ];
    public $js = [
        'js/bootstrap-tabdrop.js',
        'js/globals.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\jui\JuiAsset',
        'open20\amos\core\views\assets\IE8Assets',
        'open20\amos\core\views\assets\JqueryUiTouchPunchImprovedAsset',
        'open20\amos\core\views\assets\ConflictJuiBootstrap',
        'yii\bootstrap\BootstrapAsset',
        'kartik\select2\Select2Asset',
        'open20\amos\core\views\assets\TourAsset',
        'open20\amos\core\views\assets\AmosIconAsset',
        'open20\amos\core\views\assets\AmosFontAsset',
        'open20\amos\core\views\assets\AmosIconDashboardAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $moduleL = \Yii::$app->getModule('layout');
        if (!empty($moduleL)) {
            $this->depends = ['open20\amos\layout\assets\BaseAsset'];
            $this->css = [];
            $this->js = [];
        }
        parent::init();
    }

}
