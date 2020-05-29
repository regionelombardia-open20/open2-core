<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @licence GPLv3
 * @licence https://opensource.org/licenses/gpl-3.0.html GNU General Public License version 3
 *
 * @package amos-core
 * @category CategoryName
 * @author Lombardia Informatica S.p.A.
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
