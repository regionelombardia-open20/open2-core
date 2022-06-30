<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\utilities
 * @category   CategoryName
 */

namespace open20\amos\core\utilities;

use open20\amos\core\exceptions\AmosException;
use open20\amos\core\module\BaseAmosModule;

/**
 * Class ArrayUtility
 * @package open20\amos\core\utilities
 */
class ArrayUtility
{
    /**
     * This method translate the array values.
     * @param array $arrayValues
     * @param string $category
     * @param \open20\amos\core\module\BaseAmosModule $moduleClass
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

    /**
     * This method checks if the param is an array and all array values are strings.
     * @param array $arrayToCheck
     * @return bool
     * @throws AmosException
     */
    public static function isStringArray($arrayToCheck)
    {
        if (!is_array($arrayToCheck)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#ArrayUtility_isStringArray_array_to_check_not_array'));
        }
        foreach ($arrayToCheck as $item) {
            if (!is_string($item)) {
                return false;
            }
        }
        return true;
    }
}
