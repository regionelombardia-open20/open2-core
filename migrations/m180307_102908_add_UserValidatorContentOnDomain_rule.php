<?php

use lispa\amos\core\migration\AmosMigrationPermissions;

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
                    'ruleName' => lispa\amos\core\rules\UserValidatorContentOnDomain::className(),
                ]
            ]
        ];
    }
}
