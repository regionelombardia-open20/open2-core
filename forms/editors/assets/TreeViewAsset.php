<?php

namespace open20\amos\core\forms\editors\assets;

use yii\web\AssetBundle;

class TreeViewAsset extends AssetBundle
{
    /**
     * @var type
     */
    public $sourcePath = '@vendor/open20/amos-core/forms/editors/assets/web';

    /**
     * @var type
     */
    public $css = [
        'less/core-tree-view.less',
        'less/core-organization-view.less'
    ];

    /**
     * @var type
     */
    public $js = [

    ];

    /**
     * @var type
     */
    public $depends = [
        // 'yii\web\JqueryAsset'
    ];

    /**
     *
     */
    public function init()
    {
        parent::init();
    }

}