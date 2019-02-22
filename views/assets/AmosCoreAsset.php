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

class AmosCoreAsset extends AssetBundle
{
    public $sourcePath = '@vendor/lispa/amos-core/views/assets/web';
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
        'lispa\amos\core\views\assets\IE8Assets',
        'lispa\amos\core\views\assets\JqueryUiTouchPunchImprovedAsset',
        'lispa\amos\core\views\assets\ConflictJuiBootstrap',
        'yii\bootstrap\BootstrapAsset',
        'kartik\select2\Select2Asset',
        'lispa\amos\core\views\assets\TourAsset',
        'lispa\amos\core\views\assets\AmosIconAsset',
        'lispa\amos\core\views\assets\AmosFontAsset',
        'lispa\amos\core\views\assets\AmosIconDashboardAsset',
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $moduleL = \Yii::$app->getModule('layout');
        if (!empty($moduleL)) {
            $this->depends = ['lispa\amos\layout\assets\BaseAsset'];
            $this->css = [];
            $this->js = [];
        }
        parent::init();
    }

}
