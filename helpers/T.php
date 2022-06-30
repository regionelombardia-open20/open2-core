<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\helpers
 * @category   CategoryName
 */

namespace open20\amos\core\helpers;


use Yii;
use yii\base\BaseObject;
use open20\amos\core\module\BaseAmosModule;

class T extends BaseObject
{
    public static $category = '';
    public static $defaultCategory = 'app';
    public static $categorySeparator = '/';

    public static function getCategory()
    {
        if (Yii::$app->controller->module->id) {
            self::$category .= Yii::$app->controller->module->id . self::$categorySeparator;
        }
        if (Yii::$app->controller->id) {
            self::$category .= Yii::$app->controller->id . self::$categorySeparator;
        }
        if (Yii::$app->controller->action->id) {
            self::$category .= Yii::$app->controller->action->id;
        }
        return self::$category;

    }

    public static function tDyn($message, $params = [])
    {
        return BaseAmosModule::t(self::getCategory(), $message, $params);
    }

    public static function tApp($message, $params = [])
    {
        return BaseAmosModule::t(self::$defaultCategory, $message, $params);
    }

    public static function t($cat, $message, $params = [])
    {
        return BaseAmosModule::t($cat, $message, $params);
    }
}