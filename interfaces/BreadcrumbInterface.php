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

interface BreadcrumbInterface
{
    /**
     * @return String []
     */
    public function getIndexActions();

    /**
     * @return String [$controller => $name]
     */
    public function getControllerNames();
}