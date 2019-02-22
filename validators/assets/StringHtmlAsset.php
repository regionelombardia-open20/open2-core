<?php
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\validators
 * @category   CategoryName
 *
 */

namespace lispa\amos\core\validators\assets;

use yii\web\AssetBundle;

class StringHtmlAsset extends AssetBundle
{
    public $sourcePath = '@vendor/lispa/amos-core/validators/assets';
    public $js = [
        'js/yii.validation.texteditor.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
