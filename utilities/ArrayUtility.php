<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\utilities
 * @category   CategoryName
 */

namespace lispa\amos\core\utilities;

use lispa\amos\core\module\BaseAmosModule;

/**
 * Class ArrayUtility
 * @package lispa\amos\core\utilities
 */
class ArrayUtility
{
    /**
     * This method translate the array values.
     * @param array $arrayValues
     * @param string $category
     * @param \lispa\amos\core\module\BaseAmosModule $moduleClass
     * @return array
     */
    public static function translateArrayValues($arrayValues, $category = '', $moduleClass = null)
    {
        $translatedArrayValues = [];
        if (!$category) {
            $category = 'amoscore';
        }
        if (is_null($moduleClass)) {
            $moduleClass = BaseAmosModule::className();
        }
        foreach ($arrayValues as $index => $value) {
            $translatedArrayValues[$index] = $moduleClass::t($category, $value);
        }
        return $translatedArrayValues;
    }
}
