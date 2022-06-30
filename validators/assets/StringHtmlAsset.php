<?php
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\validators
 * @category   CategoryName
 *
 */

namespace open20\amos\core\validators\assets;

use yii\web\AssetBundle;

class StringHtmlAsset extends AssetBundle
{
    public $sourcePath = '@vendor/open20/amos-core/validators/assets';
    public $js = [
        'js/yii.validation.texteditor.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
