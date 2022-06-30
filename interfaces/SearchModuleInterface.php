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

/**
 * Interface SearchModuleInterface
 * @package open20\amos\core\interfaces
 */
interface SearchModuleInterface extends ModuleIconInterface
{
    /**
     * @return string The name of the search model class
     */
    public static function getModelSearchClassName();
}
