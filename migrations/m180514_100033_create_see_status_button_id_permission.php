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
 * Class m180514_100033_create_see_status_button_id_permission
 */
class m180514_100033_create_see_status_button_id_permission extends AmosMigrationPermissions
{
    /**
     * @inheritdoc
     */
    protected function setRBACConfigurations()
    {
        return [
            [
                'name' => 'SEE_STATUS_BUTTON_ID',
                'type' => Permission::TYPE_PERMISSION,
                'description' => 'Permission to see the change workflow status button ID'
            ]
        ];
    }
}
