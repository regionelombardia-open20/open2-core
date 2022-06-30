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
 * Class m171218_115415_permission_content_creator_validator
 */
class m171218_115415_permission_content_creator_validator extends AmosMigrationPermissions
{
    /**
     * @inheritdoc
     */
    protected function setRBACConfigurations()
    {
        return [
            [
                'name' => 'ContentCreatorOnDomain',
                'type' => Permission::TYPE_PERMISSION,
                'ruleName' => \open20\amos\core\rules\UserCreatorContentOnDomain::className(),
                'description' => 'Permission to create contents on a specific domain',
            ],
            [
                'name' => 'ContentValidatorOnDomain',
                'type' => Permission::TYPE_PERMISSION,
                'ruleName' => \open20\amos\core\rules\UserValidatorContentRule::className(),
                'description' => 'Permission to update contents on a specific domain',
            ]
        ];
    }

}
