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
 * Interface BaseContentModelInterface
 * @package open20\amos\core\interfaces
 */
interface BaseContentModelInterface
{
    /**
     * @return string The model title field value
     */
    public function getTitle();

    /**
     * @return string The model short description field value
     */
    public function getShortDescription();

    /**
     * @param bool $truncate If true the description will be truncated in order of your method implementation logic.
     * @return string The model description field value
     */
    public function getDescription($truncate);
}
