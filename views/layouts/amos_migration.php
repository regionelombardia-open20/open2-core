<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views\layouts
 * @category   CategoryName
 */

/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/* @var $className string the new migration class name */

echo "<?php\n";
?>

use lispa\amos\core\migration\AmosMigrationPermissions;
use yii\rbac\Permission;

class <?= $className ?> extends AmosMigrationPermissions
{
    /**
     * Use this function to map permissions, roles and associations between permissions and roles. If you don't need to
     * to add or remove any permissions or roles you have to delete this method.
     */
    protected function setAuthorizations()
    {
        $this->authorizations = [
            [
                'name' => 'ROLE_ONE',
                'type' => Permission::TYPE_ROLE,
                'description' => 'Role description',
                'ruleName' => null,     // This is a string
            ],
            [
                'name' => 'ROLE_TWO',
                'type' => Permission::TYPE_ROLE,
                'description' => 'Role description',
                'ruleName' => null,     // This is a string
                'parent' => ['ROLE_ONE']
            ],
            [
                'name' => 'PERMISSION_NAME',
                'type' => Permission::TYPE_PERMISSION,
                'description' => 'Permission description',
                'ruleName' => null,     // This is a string
                'parent' => ['ROLE_ONE']
            ],
            [
                'name' => 'PERMISSION_NAME',
                'type' => Permission::TYPE_PERMISSION,
                'description' => 'Permission description',
                'ruleName' => null,     // This is a string
                'parent' => ['ROLE_ONE', 'ROLE_TWO']
            ],
        ];
    }
}
