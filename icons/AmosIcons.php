<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\icons
 * @category   CategoryName
 */

namespace open20\amos\core\icons;

use kartik\icons\Icon;
use Yii;
use yii\helpers\Html;

class AmosIcons extends Icon
{
    /**
     * Icon framework constants
     */
    const AM   = 'am';
    const DASH = 'dash';
    const IC = 'ic';

    /**
     * Icon framework configurations
     */
    public static $_custom_frameworks = [
        self::AM => ['prefix' => 'am am-', 'class' => '\\open20\\amos\\layout\\assets\\BaseAsset'],
        self::DASH => ['prefix' => 'dash dash-', 'class' => '\\open20\\amos\\layout\\assets\\BaseAsset'],
        self::IC => ['prefix' => 'ic ic-', 'class' => '\\open20\\amos\\layout\\assets\\BaseAsset'],
    ];

    /**
     * @param string|null $iconFramework
     * @return string|null
     */
    public static function getIconFramework($iconFramework = null)
    {
        if (empty($iconFramework)) {
            if (isset(Yii::$app->params['icon-framework'])) {
                if (!empty(Yii::$app->params['icon-framework'])) {
                    $iconFramework = Yii::$app->params['icon-framework'];
                }
            } else {
                $iconFramework = self::AM;
            }
        }
        
        return $iconFramework;
    }

    /**
     * @param string $name
     * @param array $options
     * @param string|null $framework
     * @param bool $space
     * @param string $tag
     * @param string $value
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function show($name, $options = [], $framework = null, $space = true, $tag = 'span', $value = '')
    {
        $key = self::getFramework($framework);
        if (in_array($key, array_keys(self::$_custom_frameworks))) {
            $class = self::$_custom_frameworks[$key]['prefix'] . $name;
            Html::addCssClass($options, $class);
            
            return Html::tag($tag, ' ' . $value, $options) . ($space ? ' ' : '');
        }
        
        return parent::show($name, $options);
    }

    /**
     * @param string|null $framework
     * @param string $method
     * @return string|null
     * @throws \yii\base\InvalidConfigException
     */
    protected static function getFramework($framework = null, $method = 'show')
    {
        self::setFramework();
        $iconFramework = self::getIconFramework();
        if (strlen($framework) == 0 && !empty($iconFramework)) {
            if (in_array($iconFramework, array_keys(self::$_custom_frameworks))) {
                return $iconFramework;
            }
        } else {
            if (!in_array($framework, array_keys(self::$_custom_frameworks))) {
                return parent::getFramework($framework, $method);
            } else {
                return $framework;
            }
        }

        return parent::getFramework($framework, $method);
    }

    /**
     * @param \yii\web\View $view
     * @param string|null $framework
     * @throws \yii\base\InvalidConfigException
     */
    public static function map($view, $framework = null)
    {
        $key = self::getFramework($framework, 'map');

        if (in_array($key, array_keys(self::$_custom_frameworks))) {
            $class = self::$_custom_frameworks[$key]['class'];
            if (substr($class, 0, 1) != '\\') {
                $class = self::NS . $class;
            }

            $class::register($view);
        } else {
            parent::map($view, $framework);
        }
    }

    /**
     *
     */
    public static function setFramework()
    {
        $moduleL = \Yii::$app->getModule('layout');
        if (empty($moduleL)) {
            self::$_custom_frameworks = [
                self::AM => ['prefix' => 'am am-', 'class' => '\\open20\\amos\\core\\views\\assets\\AmosCoreAsset'],
                self::DASH => ['prefix' => 'dash dash-', 'class' => '\\open20\\amos\\core\\views\\assets\\AmosCoreAsset'],
            ];
        }
    }
}
