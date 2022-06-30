<?php
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 * @licence GPLv3
 * @licence https://opensource.org/proscriptions/gpl-3.0.html GNU General Public Proscription version 3
 *
 * @package amos-core
 * @category CategoryName
 */
namespace open20\amos\core\views\assets;

use yii\web\AssetBundle;

class PasswordInputAsset extends AssetBundle
{
    public $sourcePath = '@vendor/open20/amos-core/views/assets/web';

    public $css = [
        'css/less/password-input.less'
    ];
    public $js = [
        'js/password-input.js',
    ];
    public $depends = [
        'open20\amos\core\views\assets\AmosCoreAsset'
    ];
}
