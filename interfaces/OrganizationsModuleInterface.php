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
 * Interface OrganizationsModuleInterface
 * @package lispa\amos\core\interfaces
 */
interface OrganizationsModuleInterface
{

    public function getOrganizationModelClass();
    public function getOrganizationCardWidgetClass();
    public function getAssociateOrgsToProjectWidgetClass();
    public function getAssociateOrgsToProjectTaskWidgetClass();
    public function getOrganizationsListQuery();
    public function saveOrganizationUserMm($user_id, $organization_id);
    public function getOrganization($id);

}