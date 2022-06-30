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
 * Interface PublicationDateFieldsInterface
 * @package open20\amos\core\interfaces
 */
interface PublicationDateFieldsInterface extends BaseContentModelInterface, WorkflowModelInterface, ModelLabelsInterface
{
    /**
     * This method returns the name of the publication date begin field
     * @return string
     */
    public function getPublicatedFromField();
    
    /**
     * This method returns the name of the publication date end field
     * @return string
     */
    public function getPublicatedAtField();
    
    /**
     * This method returns true if the publication date fields are datetime instead of only date fields
     * @return bool
     */
    public function theDatesAreDatetime();
}
