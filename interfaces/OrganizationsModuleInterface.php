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
 * Interface OrganizationsModuleInterface
 * @package open20\amos\core\interfaces
 */
interface OrganizationsModuleInterface
{
    /**
     * @return string
     */
    public function getOrganizationModelClass();

    /**
     * @return string
     */
    public function getOrganizationCardWidgetClass();

    /**
     * @return string
     */
    public function getAssociateOrgsToProjectWidgetClass();

    /**
     * @return string
     */
    public function getAssociateOrgsToProjectTaskWidgetClass();

    /**
     * @return OrganizationsModelInterface[]
     */
    public function getOrganizationsListQuery();

    /**
     * @param int $user_id
     * @param int $organization_id
     * @return bool
     */
    public function saveOrganizationUserMm($user_id, $organization_id);

    /**
     * @param int $id
     * @return OrganizationsModelInterface|null
     */
    public function getOrganization($id);

    /**
     * This method returns all the organizations of an user.
     * @param int $userId
     * @return OrganizationsModelInterface[]
     */
    public function getUserOrganizations($userId);

    /**
     * This method returns all the headquarters of an user, if the module has headquarters.
     * @param int $userId
     * @return OrganizationsModelInterface[]
     */
    public function getUserHeadquarters($userId);
}
