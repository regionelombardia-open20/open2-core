<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core
 * @category   CategoryName
 */

namespace open20\amos\core\assets;

use yii\web\AssetBundle;

class TnyMentionAsset extends AssetBundle
{
    public $sourcePath = '@vendor/npm-asset/tinymce-mention/';

    public $css = [
        'css/autocomplete.css',
        'css/rte-content.css'
    ];
    
    public $js = [
        'mention/plugin.min.js'
    ];

    public $depends = [
        'dosamigos\tinymce\TinyMceAsset'
    ];
}
