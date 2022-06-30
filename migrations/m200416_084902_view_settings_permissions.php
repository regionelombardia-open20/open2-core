<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\migrations
 * @category   CategoryName
 */

use open20\amos\core\migration\AmosMigrationPermissions;
use yii\rbac\Permission;

/**
 * Class m200416_084902_view_settings_permissions
 */
class m200416_084902_view_settings_permissions extends AmosMigrationPermissions
{
    /**
     * @inheritdoc
     */
    protected function setRBACConfigurations()
    {
        return [
            [
                'name' => 'VIEW_SETTINGS',
                'type' => Permission::TYPE_PERMISSION,
                'description' => 'Permesso per vedere la rotellina settings',
                'parent' => ['ADMIN', 'BASIC_USER']
            ],
        ];
    }
}
