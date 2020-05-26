<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    Open20Package
 * @category   CategoryName
 */

use open20\amos\core\migration\AmosMigrationPermissions;

class m180307_102908_add_UserValidatorContentOnDomain_rule extends AmosMigrationPermissions
{
    /**
     * @inheritdoc
     */
    protected function setRBACConfigurations()
    {
        return [
            [
                'name' => 'ContentValidatorOnDomain',
                'update' => true,
                'newValues' => [
                    'ruleName' => open20\amos\core\rules\UserValidatorContentOnDomain::className(),
                ]
            ]
        ];
    }
}
