<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\interfaces
 * @category   CategoryName
 */

namespace lispa\amos\core\interfaces;

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
