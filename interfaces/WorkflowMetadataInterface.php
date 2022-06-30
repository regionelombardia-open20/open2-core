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
 * Interface WorkflowMetadataInterface
 * @package open20\amos\core\interfaces
 */
interface WorkflowMetadataInterface
{
    /**
     * @return string The key to be used to search the status label in the model workflow metadata.
     */
    public function getMetadataLabelKey();

    /**
     * @return string The key to be used to search the status button label in the model workflow metadata.
     */
    public function getMetadataButtonLabelKey();

    /**
     * @return string The key to be used to search the status description in the model workflow metadata.
     */
    public function getMetadataDescriptionKey();

    /**
     * @return string The key to be used to search the status button data confirm message in the model workflow metadata.
     */
    public function getMetadataButtonMessageKey();
}
