<?php
use open20\amos\core\migration\AmosMigrationPermissions;
use yii\rbac\Permission;


/**
* Class m190211_144208_models_classname_permissions*/
class m190211_144208_models_classname_permissions extends AmosMigrationPermissions
{

    /**
    * @inheritdoc
    */
    protected function setRBACConfigurations()
    {
        $prefixStr = '';

        return [
                [
                    'name' =>  'MODELS_ADMINISTRATOR',
                    'type' => Permission::TYPE_PERMISSION,
                    'description' => 'Permesso di CREATE sul model ModelsClassname',
                    'ruleName' => null,
                    'parent' => ['ADMIN']
                ],
                [
                    'name' =>  'MODELSCLASSNAME_CREATE',
                    'type' => Permission::TYPE_PERMISSION,
                    'description' => 'Permesso di CREATE sul model ModelsClassname',
                    'ruleName' => null,
                    'parent' => ['MODELS_ADMINISTRATOR']
                ],
                [
                    'name' =>  'MODELSCLASSNAME_READ',
                    'type' => Permission::TYPE_PERMISSION,
                    'description' => 'Permesso di READ sul model ModelsClassname',
                    'ruleName' => null,
                    'parent' => ['MODELS_ADMINISTRATOR']
                    ],
                [
                    'name' =>  'MODELSCLASSNAME_UPDATE',
                    'type' => Permission::TYPE_PERMISSION,
                    'description' => 'Permesso di UPDATE sul model ModelsClassname',
                    'ruleName' => null,
                    'parent' => ['MODELS_ADMINISTRATOR']
                ],
                [
                    'name' =>  'MODELSCLASSNAME_DELETE',
                    'type' => Permission::TYPE_PERMISSION,
                    'description' => 'Permesso di DELETE sul model ModelsClassname',
                    'ruleName' => null,
                    'parent' => ['MODELS_ADMINISTRATOR']
                ],

            ];
    }
}
