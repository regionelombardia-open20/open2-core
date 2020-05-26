<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\migrations
 * @category   CategoryName
 */

use open20\amos\core\migration\AmosMigrationPermissions;
use yii\rbac\Permission;

/**
 * Class m170117_142218_add_translation_administrator_permission
 */
class m170117_142218_add_translation_administrator_permission extends AmosMigrationPermissions
{
    /**
     * Use this function to map permissions, roles and associations between permissions and roles. If you don't need to
     * to add or remove any permissions or roles you have to delete this method.
     */
    protected function setAuthorizations()
    {
        $this->authorizations = [
            [
                'name' => 'TRANSLATION_ADMINISTRATOR',
                'type' => Permission::TYPE_PERMISSION,
                'description' => 'Translation administrator permission',
                'ruleName' => null,     // This is a string
                'parent' => ['ADMIN']
            ]
        ];
    }
}
