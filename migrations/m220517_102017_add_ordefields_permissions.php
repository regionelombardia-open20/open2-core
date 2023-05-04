<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\news\migrations
 * @category   CategoryName
 */

use open20\amos\core\migration\AmosMigrationPermissions;
use yii\rbac\Permission;

/**
 * Class m160912_144417_add_news_permissions_roles
 */
class m220517_102017_add_ordefields_permissions extends AmosMigrationPermissions
{
    /**
     * @inheritdoc
     */
    protected function setRBACConfigurations()
    {
        return [
            [
                'name' => 'MANAGE_ORDER_FIELDS',
                'type' => Permission::TYPE_PERMISSION,
                'description' => 'Permesso per la gestione dei campi di ordinamento',
                'parent' => ['ADMIN']
            ],
        ];
    }
   
}
