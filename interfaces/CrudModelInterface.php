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

/**
 * Interface CrudModelInterface
 * @package lispa\amos\core\interfaces
 */
interface CrudModelInterface extends ViewModelInterface
{
    /**
     * @return string The url to create a single model
     */
    public function getCreateUrl();

    /**
     * @return string The url to "create" action for this model
     */
    public function getFullCreateUrl();

    /**
     * @return string The url to update a single model
     */
    public function getUpdateUrl();

    /**
     * @return string The url to "update" action for this model
     */
    public function getFullUpdateUrl();

    /**
     * @return string The url to delete a single model
     */
    public function getDeleteUrl();

    /**
     * @return string The url to "delete" action for this model
     */
    public function getFullDeleteUrl();
}
