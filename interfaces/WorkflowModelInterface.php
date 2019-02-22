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


interface WorkflowModelInterface
{

    /**
     * @return string The name that correspond to 'to validate' status for the content model
     */
    public function getToValidateStatus();

    /**
     * @return string The name that correspond to 'published' status for the content model
     */
    public function getValidatedStatus();

    /**
     * @return string The name that correspond to 'draft' status for the content model
     */
    public function getDraftStatus();

    /**
     * @return string The name of model validator role
     */
    public function getValidatorRole();

    /**
     * @return array list of statuses that for cwh is validated
     */
    public function getCwhValidationStatuses();



}