<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\interfaces
 * @category   CategoryName
 */

namespace open20\amos\core\interfaces;


interface SearchModuleInterface
{

    /**
     * @return string The name of the search model class
     */
    public static function getModelSearchClassName();

    /**
     * @return string The module icon name
     */
    public static function getModuleIconName();
}