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
 * Interface ApplicationInterface
 * @package open20\amos\core\interfaces
 */
interface ApplicationInterface
{
    /**
     * @return bool
     */
    public function isConsoleApplication();
    
    /**
     * @return bool
     */
    public function isBackendApplication();
    
    /**
     * @return bool
     */
    public function isCmsApplication();
    
    /**
     * @return bool
     */
    public function isBasicAuthEnabled();
}
