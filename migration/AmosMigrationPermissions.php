<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\migration
 * @category   CategoryName
 */

namespace open20\amos\core\migration;

use open20\amos\core\migration\libs\common\MigrationCommon;
use open20\amos\core\migration\libs\permissions\UpdatePermission;
use open20\amos\core\module\BaseAmosModule;
use Yii;
use yii\db\Migration;
use yii\rbac\DbManager;
use yii\rbac\Permission;

/**
 * Class AmosMigrationPermissions
 *
 * If you don't want to remove a permission or a role you must set the "dontRemove" key to true in every array of a single permission or role.
 *
 * @package open20\amos\core\migration
 */
class AmosMigrationPermissions extends Migration
{
    const FIELD_TYPE_STRING = 'STRING';
    const FIELD_TYPE_INT = 'INT';
    const FIELD_TYPE_ARRAY = 'ARRAY';
    const FIELD_TYPE_BOOL = 'BOOL';
    
    /**
     * @var array $authorizations An array where you can insert permissions and roles that will be inserted in the database.
     */
    protected $authorizations;
    
    /**
     * @var bool $_processInverted If true switch safeUp and safeDown operations. This mean that in up the permissions are removed and in down the permissions are added.
     */
    private $_processInverted = false;
    
    /**
     * @var array $fieldsToCheck This is internal configurations useful to check the integrity of the array content.
     */
    private $fieldsToCheck = [
//        'old_name' => self::FIELD_TYPE_STRING,
        'name' => self::FIELD_TYPE_STRING,
        'type' => self::FIELD_TYPE_INT,
        'description' => self::FIELD_TYPE_STRING,
        'ruleName' => self::FIELD_TYPE_STRING,
        'parent' => self::FIELD_TYPE_ARRAY,
        'children' => self::FIELD_TYPE_ARRAY,
        'update' => self::FIELD_TYPE_BOOL,
        'replace' => self::FIELD_TYPE_BOOL,
        'cleanParents' => self::FIELD_TYPE_BOOL,
        'oldValues' => self::FIELD_TYPE_ARRAY,
        'newValues' => self::FIELD_TYPE_ARRAY,
    ];
    
    /**
     * @var array $createRequiredFields
     */
    private $requiredFields = [
        'name',
        'type'
    ];
    
    /**
     * @var array $updateRequiredFields
     */
    private $updateRequiredFields = [
        'name'
    ];
    
    /**
     * @var array $specialFieldToCheck
     */
    private $specialFieldToCheck = [
//        'old_name',
        'ruleName',
        'parent',
        'children',
        'update',
        'replace',
        'cleanParents',
        'oldValues',
        'newValues'
    ];
    
    /**
     * @var array $authItemSettableFields
     */
    private $authItemSettableFields = [
        'name',
        'type',
        'description'
    ];
    
    private $updateOnlyFields = [
        'name',
        'oldValues',
        'newValues',
//        'parent',
//        'cleanParents'
    ];
    
    private $updateSkipCheckFields = [
        'update'
    ];
    
    /**
     * @var DbManager $authManager The Yii app auth manager based on DbManager rbac class.
     */
    private $authManager = null;
    
    /**
     * @return bool
     */
    public function isProcessInverted()
    {
        return $this->_processInverted;
    }
    
    /**
     * @param bool $processInverted
     */
    public function setProcessInverted($processInverted)
    {
        $this->_processInverted = $processInverted;
    }
    
    /**
     */
    public function init()
    {
        parent::init();
        $this->db->enableSchemaCache = false;
        $this->authManager = Yii::$app->getAuthManager();
        $this->setAuthorizations();
        $this->authorizations = $this->setRBACConfigurations();
    }
    
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->isProcessInverted()) {
            return $this->removeAuthorizations();
        } else {
            return $this->addAuthorizations();
        }
    }
    
    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        if ($this->isProcessInverted()) {
            return $this->addAuthorizations();
        } else {
            return $this->removeAuthorizations();
        }
    }
    
    /**
     * @deprecated Don't override this method!!!
     */
    protected function setAuthorizations()
    {
        $this->authorizations = [];
    }
    
    /**
     * Set your new authorizations here. You can see a configurations array example below.
     *
     *   $this->authorizations = [
     *       [
     *           'name' => 'ROLE_ONE',
     *           'type' => Permission::TYPE_ROLE,
     *           'description' => 'Role description',
     *           'ruleName' => null,     // This is a string
     *       ],
     *       [
     *           'name' => 'ROLE_TWO',
     *           'type' => Permission::TYPE_ROLE,
     *           'description' => 'Role description',
     *           'ruleName' => null,     // This is a string
     *           'parent' => ['ROLE_ONE']
     *       ],
     *       [
     *           'name' => 'PERMISSION_NAME',
     *           'type' => Permission::TYPE_PERMISSION,
     *           'description' => 'Permission description',
     *           'ruleName' => null,     // This is a string
     *           'parent' => ['ROLE_ONE']
     *       ],
     *       [
     *           'name' => 'PERMISSION_NAME',
     *           'type' => Permission::TYPE_PERMISSION,
     *           'description' => 'Permission description',
     *           'ruleName' => null,     // This is a string
     *           'parent' => ['ROLE_ONE', 'ROLE_TWO', ...]
     *       ],
     *       .
     *       .
     *       .
     *       ];
     * @return array
     */
    protected function setRBACConfigurations()
    {
        return $this->authorizations;
    }
    
    /**
     * This is the method that parse all the authorizations array and add them to the system. It checks the data
     * integrity and add permissions, roles and associations between them.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    private function addAuthorizations()
    {
        $ok = $this->checkArrayStructure();
        if ($ok)
            $ok = $this->addPermissionsAndRoles();
        if ($ok)
            $ok = $this->addItemChilds();
        return $ok;
    }
    
    /**
     * Method to check the authorizations array data integrity. It checks if there's any missing field or if a field
     * does not contain the right value type.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    private function checkArrayStructure()
    {
        if (!is_array($this->authorizations)) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Authorizations must be an array. Check the migration!'));
            return false;
        }

//        foreach ($this->authorizations as $authorization) {
//            if (isset($authorization['update'])) {
//                $ok = $this->checkUpdateFields($authorization);
//                if ($ok) {
//                    $ok = $this->checkRequiredFields($authorization, $this->updateRequiredFields);
//                }
//                if (!$ok) {
//                    return false;
//                }
//            } else {
//                $ok = $this->checkRequiredFields($authorization, $this->requiredFields);
//                if (!$ok) {
//                    return false;
//                }
//            }
//            $ok = $this->checkAllowedFieldsAndFieldTypes($authorization);
//            if (!$ok) {
//                return false;
//            }
//        }
        return true;
    }

//    /**
//     * @param array $authorization
//     * @return bool
//     */
//    private function checkUpdateFields($authorization)
//    {
//        $authFields = array_keys($authorization);
//        $ok = true;
//        foreach ($authFields as $fieldName) {
//            if (in_array($fieldName, $this->updateSkipCheckFields)) {
//                continue;
//            }
//            if (!in_array($fieldName, $this->updateOnlyFields)) {
//                $this->printCheckStructureError($authorization, BaseAmosModule::t('amoscore', "Campo non consentito per l'update") . ": '$fieldName' ");
//                $ok = false;
//            }
//        }
//        if (!in_array('name', $authFields)) {
//            $this->printCheckStructureError($authorization, BaseAmosModule::t('amoscore', "Campo obbligatorio non presente nell'array") . ": 'name' ");
//            $ok = false;
//        }
//        return $ok;
//    }
//
//    /**
//     * @param array $authorization
//     * @param array $requiredFields
//     * @return bool
//     */
//    private function checkRequiredFields($authorization, $requiredFields)
//    {
//        $authFields = array_keys($authorization);
//        foreach ($requiredFields as $requiredFieldName) {
//            if (!in_array($requiredFieldName, $authFields)) {
//                $this->printCheckStructureError($authorization, BaseAmosModule::t('amoscore', "Campo obbligatorio non presente nell'array") . ": '$requiredFieldName' ");
//                return false;
//            }
//        }
//        return true;
//    }
//
//    /**
//     * @param array $authorization
//     * @return bool
//     */
//    private function checkAllowedFieldsAndFieldTypes($authorization)
//    {
//        $fieldsNamesToCheck = array_keys($this->fieldsToCheck);
//        foreach ($authorization as $fieldName => $fieldType) {
//            if (!in_array($fieldName, $fieldsNamesToCheck)) {
//                $this->printCheckStructureError($authorization, BaseAmosModule::t('amoscore', 'Campo') . " '$fieldName' " . BaseAmosModule::t('amoscore', "non consentito") . '.');
//                return false;
//            }
//            if (!$this->checkFieldType($fieldName, $this->fieldsToCheck[$fieldName], $authorization)) {
//                $this->printCheckStructureError($authorization, BaseAmosModule::t('amoscore', 'Contenuto campo') . " '$fieldName' " . BaseAmosModule::t('amoscore', "del tipo errato. Dev'essere") . " $fieldType.");
//                return false;
//            }
//        }
//        return true;
//    }
//
//    /**
//     * Method that checks the correct type of a field value.
//     *
//     * @param string $fieldName Name of an internal array field.
//     * @param string $fieldType Value type of an internal array field.
//     * @param array $authorization One internal array.
//     *
//     * @return bool Returns true if everything goes well. False otherwise.
//     */
//    private function checkFieldType($fieldName, $fieldType, $authorization)
//    {
//        if (in_array($fieldName, $this->specialFieldToCheck) && is_null($authorization[$fieldName])) {
//            return true;
//        }
//        switch ($fieldType) {
//            case self::FIELD_TYPE_STRING:
//                $ok = is_string($authorization[$fieldName]);
//                break;
//            case self::FIELD_TYPE_INT:
//                $ok = is_numeric($authorization[$fieldName]);
//                break;
//            case self::FIELD_TYPE_ARRAY:
//                $ok = is_array($authorization[$fieldName]);
//                break;
//            case self::FIELD_TYPE_BOOL:
//                $ok = is_bool($authorization[$fieldName]);
//                break;
//            default:
//                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "Invalid type for field") . " '$fieldName'");
//                $ok = false;
//                break;
//        }
////        MigrationCommon::printConsoleMessage("Campo $fieldName di tipo $fieldType: " . ($ok ? ' ' : ' non ') . "ok");
//        return $ok;
//   }
    
    /**
     * Method that insert all permissions and roles in the authorizations array.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    private function addPermissionsAndRoles()
    {
        $allOk = true;
        foreach ($this->authorizations as $authorization) {
            if (isset($authorization['replace']) && isset($authorization['update'])) {
                MigrationCommon::printCheckStructureError($authorization, BaseAmosModule::t('amoscore', "The keys 'replace' and 'update' are not permitted together."));
            } elseif (isset($authorization['replace'])) {
                // TODO to devel
                MigrationCommon::printConsoleMessage('TODO replace');
            } elseif (isset($authorization['update'])) {
                $update = new UpdatePermission(['authorization' => $authorization]);
                $ok = $update->updateAuthorization();
                if (!$ok) {
                    $allOk = false;
                }
            } else {
                if ($authorization['type'] == Permission::TYPE_PERMISSION) {
                    $ok = $this->createPermission($authorization);
                    if (!$ok) {
                        $allOk = false;
                    }
                } elseif ($authorization['type'] == Permission::TYPE_ROLE) {
                    $ok = $this->createRole($authorization);
                    if (!$ok) {
                        $allOk = false;
                    }
                }
            }
        }
        return $allOk;
    }
    
    /**
     * Method that insert a single permission in the system. It print a message if permission exists or permission
     * is successfully created or an error occurred.
     *
     * @param array $authorization One internal array.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    private function createPermission($authorization)
    {
        // Rule creation
        $ruleName = $this->createRule($authorization);
        
        // Verify if permission exists
        $permissionName = $authorization['name'];
        $perm = $this->authManager->getPermission($permissionName);
        if (!is_null($perm)) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Permission') . " '$permissionName' " . BaseAmosModule::t('amoscore', 'exists. Skipping...'));
            return true;
        }
        
        // Add permission
        $perm = $this->authManager->createPermission($permissionName);
        $perm->description = $authorization['description'];
        $perm->ruleName = $ruleName;
        $ok = $this->authManager->add($perm);
        
        if ($ok) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Permission') . " '$permissionName' " . BaseAmosModule::t('amoscore', 'successfully created.'));
        } else {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Error creating permission') . " '$permissionName'.");
        }
        
        return $ok;
    }
    
    /**
     * Method that insert a single role in the system. It print a message if role exists or role is successfully
     * created or an error occurred.
     *
     * @param array $authorization One internal array.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    private function createRole($authorization)
    {
        // Rule creation
        $ruleName = $this->createRule($authorization);
        
        // Verify if role exists
        $roleName = $authorization['name'];
        if (!is_null($this->authManager->getRole($roleName))) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Role') . " '$roleName' " . BaseAmosModule::t('amoscore', 'exists. Skipping...'));
            return true;
        }
        
        // Add role
        $role = $this->authManager->createRole($roleName);
        $role->description = $authorization['description'];
        $role->ruleName = $ruleName;
        $ok = $this->authManager->add($role);
        
        if ($ok) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Role') . " '$roleName' " . BaseAmosModule::t('amoscore', 'successfully created.'));
        } else {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Error creating role') . " '$roleName'.");
        }
        
        return $ok;
    }
    
    /**
     * Method that create a rule in the system. It print a message if permission exists or permission
     * is successfully created or an error occurred.
     *
     * @param array $authorization One internal array.
     *
     * @return null|string Returns rule name if successfully created or exists. Null otherwise.
     */
    private function createRule($authorization)
    {
        $ruleName = null;
        if (isset($authorization['ruleName']) && is_string($authorization['ruleName'])) {
            $ruleClassName = '\\' . $authorization['ruleName'];
            $ruleTmp = new $ruleClassName;
            $rule = $this->authManager->getRule($ruleTmp->name);
            if (is_null($rule)) {
                $rule = new $ruleClassName;
                $ok = $this->authManager->add($rule);
                if ($ok) {
                    $ruleName = $rule->name;
                    MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Rule successfully created') . ": '$ruleName'.");
                } else {
                    MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Error during rule creation') . ": '$ruleName'.");
                }
            } else {
                $ruleName = $rule->name;
            }
        }
        return $ruleName;
    }
    
    /**
     * This method creates the associations between permissions and roles.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    private function addItemChilds()
    {
        $allOk = true;
        foreach ($this->authorizations as $authorization) {
            if (isset($authorization['replace']) && isset($authorization['update'])) {
                MigrationCommon::printCheckStructureError($authorization, BaseAmosModule::t('amoscore', "The keys 'replace' and 'update' are not permitted together."));
            } elseif (isset($authorization['replace'])) {
                // TODO to devel
                MigrationCommon::printConsoleMessage('TODO replace');
            } elseif (isset($authorization['update'])) {
                $update = new UpdatePermission(['authorization' => $authorization]);
                $ok = $update->updateAuthorizationChildren();
                if (!$ok) {
                    $allOk = false;
                }
            } else {
                if (isset($authorization['parent']) && is_array($authorization['parent'])) {
                    foreach ($authorization['parent'] as $parentName) {
                        if (!is_string($parentName)) {
                            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "The parent is not a string") . ": '" . $parentName . "'. " . BaseAmosModule::t('amoscore', ". Skipping..."));
                            continue;
                        }
                        
                        $parent = $this->authManager->getRole($parentName);
                        if (is_null($parent)) {
                            $parent = $this->authManager->getPermission($parentName);
                        }
                        if (!is_null($parent)) {
                            $child = null;
                            $childStr = $authorization['name'];
                            if ($authorization['type'] == Permission::TYPE_PERMISSION) {
                                $child = $this->authManager->getPermission($childStr);
                            } elseif ($authorization['type'] == Permission::TYPE_ROLE) {
                                $child = $this->authManager->getRole($childStr);
                            }
                            
                            if (!is_null($child)) {
                                $messagePrefix = BaseAmosModule::t('amoscore', "Associations between") . " '" . $parentName . "' " . BaseAmosModule::t('amoscore', "and") . " '" . $childStr . "' ";
                                $exists = $this->authManager->hasChild($parent, $child);
                                if (!$exists) {
                                    $ok = $this->authManager->addChild($parent, $child);
                                    if ($ok) {
                                        MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', "successfully created"));
                                    } else {
                                        MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', "failed"));
                                    }
                                } else {
                                    MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', "exists. Skipping..."));
                                }
                            } else {
                                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "Child not found") . ": '" . $parentName . "'. " . BaseAmosModule::t('amoscore', "Skipping..."));
                            }
                        } else {
                            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "Parent not found") . ": '" . $parentName . "'. " . BaseAmosModule::t('amoscore', "Skipping..."));
                        }
                    }
                }
                
                if (isset($authorization['children']) && is_array($authorization['children'])) {
                    $parent = null;
                    $parentStr = $authorization['name'];
                    if ($authorization['type'] == Permission::TYPE_PERMISSION) {
                        $parent = $this->authManager->getPermission($parentStr);
                    } elseif ($authorization['type'] == Permission::TYPE_ROLE) {
                        $parent = $this->authManager->getRole($parentStr);
                    }
                    if (!is_null($parent)) {
                        foreach ($authorization['children'] as $childName) {
                            if (!is_string($childName)) {
                                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "The child is not a string") . ": '" . $childName . "'. " . BaseAmosModule::t('amoscore', ". Skipping..."));
                                continue;
                            }

                            $child = $this->authManager->getRole($childName);
                            if (is_null($child)) {
                                $child = $this->authManager->getPermission($childName);
                            }
                            if (!is_null($child)) {
                                $messagePrefix = BaseAmosModule::t('amoscore', "Associations between") . " '" . $parentStr . "' " . BaseAmosModule::t('amoscore', "and") . " '" . $childName . "' ";
                                $exists = $this->authManager->hasChild($parent, $child);
                                if (!$exists) {
                                    $ok = $this->authManager->addChild($parent, $child);
                                    if ($ok) {
                                        MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', "successfully created"));
                                    } else {
                                        MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', "failed"));
                                    }
                                } else {
                                    MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', "exists. Skipping..."));
                                }
                            } else {
                                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "Child not found") . ": '" . $childName . "'. " . BaseAmosModule::t('amoscore', "Skipping..."));
                            }
                        }
                    } else {
                        MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "Parent not found") . ": '" . $authorization['name'] . "'. " . BaseAmosModule::t('amoscore', "Skipping..."));
                    }
                }
            }
        }
        
        return $allOk;
    }
    
    /**
     * This is the method that parse all the authorizations array and remove them from the system. It checks the data
     * integrity and add permissions, roles and associations between them.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    private function removeAuthorizations()
    {
        $ok = $this->checkArrayStructure();
        if ($ok)
            $ok = $this->removeItemChilds();
        if ($ok)
            $ok = $this->removePermissionsAndRoles();
        return $ok;
    }
    
    /**
     * This method destroy the associations between permissions and roles.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    private function removeItemChilds()
    {
        $allOk = true;
        foreach ($this->authorizations as $authorization) {
            if (isset($authorization['replace']) && isset($authorization['update'])) {
                MigrationCommon::printCheckStructureError($authorization, BaseAmosModule::t('amoscore', "The keys 'replace' and 'update' are not permitted together."));
            } elseif (isset($authorization['replace'])) {
                // TODO to devel
                MigrationCommon::printConsoleMessage('TODO replace');
            } elseif (isset($authorization['update'])) {
                $update = new UpdatePermission(['authorization' => $authorization]);
                $ok = $update->revertUpdateAuthorizationChildren();
                if (!$ok) {
                    $allOk = false;
                }
            } else {
                if (isset($authorization['parent']) && is_array($authorization['parent'])) {
                    foreach ($authorization['parent'] as $parentName) {
                        if (!is_string($parentName)) {
                            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "The parent is not a string") . ": '" . $parentName . "'. " . BaseAmosModule::t('amoscore', ". Skipping..."));
                            continue;
                        }
                        
                        $parent = $this->authManager->getRole($parentName);
                        if (is_null($parent)) {
                            $parent = $this->authManager->getPermission($parentName);
                        }
                        if (!is_null($parent)) {
                            $child = null;
                            $childStr = $authorization['name'];
                            if ($authorization['type'] == Permission::TYPE_PERMISSION) {
                                $child = $this->authManager->getPermission($childStr);
                                
                            } elseif ($authorization['type'] == Permission::TYPE_ROLE) {
                                $child = $this->authManager->getRole($childStr);
                            }
                            
                            if (!is_null($child)) {
                                $messagePrefix = BaseAmosModule::t('amoscore', "Associations between") . " '" . $parentName . "' " . BaseAmosModule::t('amoscore', "and") . " '" . $childStr . "' ";
                                $exists = $this->authManager->hasChild($parent, $child);
                                if ($exists) {
                                    $ok = $this->authManager->removeChild($parent, $child);
                                    if ($ok) {
                                        MigrationCommon::printConsoleMessage($messagePrefix . " " . BaseAmosModule::t('amoscore', 'removed successfully'));
                                    } else {
                                        MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', "remove failed"));
                                    }
                                } else {
                                    MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', "not exist. Skipping remove..."));
                                }
                            } else {
                                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "Child not found") . ": '" . $parentName . "'. " . BaseAmosModule::t('amoscore', "Skipping..."));
                            }
                        } else {
                            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "Parent not found") . ": '" . $parentName . "'. " . BaseAmosModule::t('amoscore', "Skipping..."));
                        }
                    }
                }
                
                if (isset($authorization['children']) && is_array($authorization['children'])) {
                    $parent = null;
                    $parentStr = $authorization['name'];
                    if ($authorization['type'] == Permission::TYPE_PERMISSION) {
                        $parent = $this->authManager->getPermission($parentStr);
                        
                    } elseif ($authorization['type'] == Permission::TYPE_ROLE) {
                        $parent = $this->authManager->getRole($parentStr);
                    }
                    if (!is_null($parent)) {
                        foreach ($authorization['children'] as $childName) {
                            if (!is_string($childName)) {
                                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "The child is not a string") . ": '" . $childName . "'. " . BaseAmosModule::t('amoscore', ". Skipping..."));
                                continue;
                            }
                            
                            $child = $this->authManager->getRole($childName);
                            if (is_null($child)) {
                                $child = $this->authManager->getPermission($childName);
                            }
                            if (!is_null($child)) {
                                $messagePrefix = BaseAmosModule::t('amoscore', "Associations between") . " '" . $parentStr . "' " . BaseAmosModule::t('amoscore', "and") . " '" . $childName . "' ";
                                $exists = $this->authManager->hasChild($parent, $child);
                                if ($exists) {
                                    $ok = $this->authManager->removeChild($parent, $child);
                                    if ($ok) {
                                        MigrationCommon::printConsoleMessage($messagePrefix . " " . BaseAmosModule::t('amoscore', 'removed successfully'));
                                    } else {
                                        MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', "remove failed"));
                                    }
                                } else {
                                    MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', "not exist. Skipping remove..."));
                                }
                            } else {
                                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "Child not found") . ": '" . $childName . "'. " . BaseAmosModule::t('amoscore', "Skipping..."));
                            }
                        }
                    } else {
                        MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "Parent not found") . ": '" . $authorization['name'] . "'. " . BaseAmosModule::t('amoscore', "Skipping..."));
                    }
                }
            }
        }
        
        return $allOk;
    }
    
    /**
     * Method that delete all permissions and roles in the authorizations array.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    private function removePermissionsAndRoles()
    {
        $allOk = true;
        foreach ($this->authorizations as $authorization) {
            $authorizationName = $authorization['name'];
            if (isset($authorization['replace']) && isset($authorization['update'])) {
                MigrationCommon::printCheckStructureError($authorization, BaseAmosModule::t('amoscore', "The keys 'replace' and 'update' are not permitted together."));
            } elseif (isset($authorization['replace'])) {
                // TODO to devel
                MigrationCommon::printConsoleMessage('TODO replace');
            } elseif (isset($authorization['update'])) {
                $update = new UpdatePermission(['authorization' => $authorization]);
                $ok = $update->revertUpdateAuthorization();
                if (!$ok) {
                    $allOk = false;
                }
            } else {
                if ($authorization['type'] == Permission::TYPE_PERMISSION) {
                    if (isset($authorization['dontRemove']) && $authorization['dontRemove'] === true) {
                        MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'The permission must not be removed: ') . "'" . $authorizationName . "'");
                        continue;
                    }
                    $ok = $this->deletePermission($authorization);
                    if (!$ok) {
                        $allOk = false;
                    }
                } elseif ($authorization['type'] == Permission::TYPE_ROLE) {
                    if (isset($authorization['dontRemove']) && $authorization['dontRemove'] === true) {
                        MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'The role must not be removed: ') . "'" . $authorizationName . "'");
                        continue;
                    }
                    $ok = $this->deleteRole($authorization);
                    if (!$ok) {
                        $allOk = false;
                    }
                }
            }
        }
        
        return $allOk;
    }
    
    /**
     * Method that delete a single permission from the system. It print a message if permission does not exist or
     * permission is successfully deleted or an error occurred.
     *
     * @param array $authorization One internal array.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    private function deletePermission($authorization)
    {
        // Verify if permission exists
        $permissionName = $authorization['name'];
        $perm = $this->authManager->getPermission($permissionName);
        if (is_null($perm)) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Permission not exist:') . " '" . $permissionName . "' " . BaseAmosModule::t('amoscore', "Skipping..."));
            return false;
        }
        
        // Remove permission
        $ok = $this->authManager->remove($perm);
        
        if ($ok) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Permission') . " '$permissionName' " . BaseAmosModule::t('amoscore', 'successfully deleted.'));
        } else {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Error deleting permission') . " '$permissionName'.");
        }
        
        return $ok;
    }
    
    /**
     * Method that insert a single role in the system. It print a message if role exists or role is successfully
     * created or an error occurred.
     *
     * @param array $authorization One internal array.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    private function deleteRole($authorization)
    {
        // Verify if role exists
        $roleName = $authorization['name'];
        $role = $this->authManager->getRole($roleName);
        if (is_null($role)) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Role not exist:') . " '" . $roleName . "' " . BaseAmosModule::t('amoscore', "Skipping..."));
            return false;
        }
        
        // Remove role
        $ok = $this->authManager->remove($role);
        
        if ($ok) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Role') . " '$roleName' " . BaseAmosModule::t('amoscore', 'successfully deleted.'));
        } else {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Error deleting role') . " '$roleName'.");
        }
        
        return $ok;
    }

    /**
     * @param string $roleName
     * @return null|\yii\rbac\Role
     */
    protected function findRole($roleName)
    {
        $role = $this->authManager->getRole($roleName);
        if (is_null($role)) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "Ruolo '{roleName}' non trovato", ['roleName' => $roleName]));
        }
        return $role;
    }

    /**
     * This method empty a role. You can specify a strings array of children to skip remove.
     * @param string $roleName
     * @param string[] $toSkipChildren
     * @return bool
     */
    protected function emptyRole($roleName, $toSkipChildren = [])
    {
        $role = $this->findRole($roleName);
        $roleChildren = $this->authManager->getChildren($roleName);
        $ok = true;
        if (!empty($roleChildren)) {
            foreach ($roleChildren as $roleChild) {
                if (in_array($roleChild->name, $toSkipChildren)) {
                    MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "AuthItem da skippare nello svuotamento del ruolo") . ': ' . $roleChild->name);
                    continue;
                }
                $ok = $this->authManager->removeChild($role, $roleChild);
                if (!$ok) {
                    MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "Errori durante la rimozione di '{childName}' da '{roleName}'", [
                        'childName' => $roleChild->name,
                        'roleName' => $role->name
                    ]));
                    $ok = false;
                    break;
                }
            }
        }
        if ($ok) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "Il ruolo '{roleName}' è stato svuotato correttamente", ['roleName' => $role->name]));
        }
        return $ok;
    }
}
