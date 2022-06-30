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

/**
 * Interface ViewModelInterface
 * @package open20\amos\core\interfaces
 */
interface ViewModelInterface
{
    /**
     * @return string The url to view a single model
     */
    public function getViewUrl();

    /**
     * @return string The url to "view" action for this model
     */
    public function getFullViewUrl();
}
