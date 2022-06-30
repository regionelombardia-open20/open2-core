<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\interfaces
 * @category   CategoryName
 */

namespace open20\amos\core\interfaces;

interface CmsModuleInterface {

    /**
     * @return string The name of the search model class
     */
    public static function getModelSearchClassName();

    /**
     * @return string The name of the main model class
     */
    public static function getModelClassName();
}
