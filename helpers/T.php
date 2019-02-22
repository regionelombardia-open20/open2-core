<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\helpers
 * @category   CategoryName
 */

namespace lispa\amos\core\helpers;


use Yii;
use yii\base\Object;

class T extends Object
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
        return Yii::t(self::getCategory(), $message, $params);
    }

    public static function tApp($message, $params = [])
    {
        return Yii::t(self::$defaultCategory, $message, $params);
    }

    public static function t($cat, $message, $params = [])
    {
        return Yii::t($cat, $message, $params);
    }
}