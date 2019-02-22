<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\migration\libs\permissions
 * @category   CategoryName
 */

namespace lispa\amos\core\migration\libs\permissions;

use lispa\amos\core\exceptions\MigrationsException;
use lispa\amos\core\migration\libs\common\MigrationCommon;
use lispa\amos\core\module\BaseAmosModule;
use Yii;
use yii\base\Object;
use yii\rbac\DbManager;
use yii\rbac\Item;
use yii\rbac\Permission;

/**
 * Class UpdatePermission
 * @package lispa\amos\core\migration\libs
 */
class UpdatePermission extends Object
{
    const FIELD_TYPE_STRING = 'STRING';
    const FIELD_TYPE_INT = 'INT';
    const FIELD_TYPE_ARRAY = 'ARRAY';
    const FIELD_TYPE_BOOL = 'BOOL';
    
    /**
     * @var array $fieldsTypesToCheck This is internal configurations useful to check the integrity of the array content.
     */
    private $fieldsTypesToCheck = [
        'update' => self::FIELD_TYPE_BOOL,
        'name' => self::FIELD_TYPE_STRING,
        'oldValues' => self::FIELD_TYPE_ARRAY,
        'newValues' => self::FIELD_TYPE_ARRAY,
        'description' => self::FIELD_TYPE_STRING,
        'ruleName' => self::FIELD_TYPE_STRING,
        'removeParents' => self::FIELD_TYPE_ARRAY,
        'addParents' => self::FIELD_TYPE_ARRAY,
    ];
    
    /**
     * @var array $authorization
     */
    public $authorization = [];
    
    /**
     * @var Item $authItem
     */
    private $authItem = null;
    
    /**
     * @var DbManager $authManager The Yii app auth manager based on DbManager rbac class.
     */
    private $authManager = null;
    
    /**
     * @var array $requiredFields
     */
    private $requiredFields = [
        'name',
        'oldValues',
        'newValues'
    ];
    
    private $oldValuesAllowedFields = [
        'description',
        'ruleName'
    ];
    
    private $newValuesAllowedFields = [
        'description',
        'ruleName',
        'removeParents',
        'addParents'
    ];
    
    /**
     * @var bool $_processInverted If true switch safeUp and safeDown operations. This mean that in up the permissions are removed and in down the permissions are added.
     */
    private $_processInverted = false;
    
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
        'update',
        'replace',
        'cleanParents',
        'oldValues',
        'newValues'
    ];
    
    /**
     * @var array $authItemAutoSettableFields
     */
    private $authItemAutoSettableFields = [
        'description'
    ];
    
    /**
     * @var array $authItemSettableFields
     */
    private $authItemSettableFields = [
        'description',
        'ruleName'
    ];
    
    private $updateSkipCheckFields = [
        'update'
    ];
    
    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();
        $this->authManager = Yii::$app->getAuthManager();
        
        if (empty($this->authorization)) {
            throw new MigrationsException("Authorization update array empty");
        }
    }
    
    /**
     * @param string $authItemName
     * @return bool
     */
    private function instanceAuthItem($authItemName)
    {
        // Verify if permission exists
        $this->authItem = $this->authManager->getRole($authItemName);
        if (is_null($this->authItem)) {
            $this->authItem = $this->authManager->getPermission($authItemName);
        }
        if (is_null($this->authItem)) {
            return false;
        }
        return true;
    }
    
    /**
     * Method to check the authorizations array data integrity. It checks if there's any missing field or if a field
     * does not contain the right value type.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    private function checkArrayStructure()
    {
        if (!is_array($this->authorization)) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'UpdatePermission: authorization must be an array. Check the migration!'));
            return false;
        }

//        $authFields = array_keys($this->authorization);
//
//        foreach ($this->authorization as $fieldName => $fieldValue) {
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
//                MigrationCommon::printCheckStructureError($authorization, BaseAmosModule::t('amoscore', "Campo non consentito per l'update") . ": '$fieldName' ");
//                $ok = false;
//            }
//        }
//        if (!in_array('name', $authFields)) {
//            MigrationCommon::printCheckStructureError($authorization, BaseAmosModule::t('amoscore', "Campo obbligatorio non presente nell'array") . ": 'name' ");
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
//                MigrationCommon::printCheckStructureError($authorization, BaseAmosModule::t('amoscore', "Campo obbligatorio non presente nell'array") . ": '$requiredFieldName' ");
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
//                MigrationCommon::printCheckStructureError($authorization, BaseAmosModule::t('amoscore', 'Campo') . " '$fieldName' " . BaseAmosModule::t('amoscore', "non consentito") . '.');
//                return false;
//            }
//            if (!$this->checkFieldType($fieldName, $this->fieldsToCheck[$fieldName], $authorization)) {
//                MigrationCommon::printCheckStructureError($authorization, BaseAmosModule::t('amoscore', 'Contenuto campo') . " '$fieldName' " . BaseAmosModule::t('amoscore', "del tipo errato. Dev'essere") . " $fieldType.");
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
//                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "Invalid type for field '$fieldName'"));
//                $ok = false;
//                break;
//        }
////        MigrationCommon::printConsoleMessage("Campo $fieldName di tipo $fieldType: " . ($ok ? ' ' : ' non ') . "ok");
//        return $ok;
//    }
    
    /**
     * This method update the authorization.
     * @return bool
     */
    public function updateAuthorization()
    {
        $ok = $this->checkArrayStructure();
//        if ($ok) {
//            MigrationCommon::printConsoleMessage("Struttura ok");
//        }
//        die();
        if ($ok) {
            $ok = $this->instanceAuthItem($this->authorization['name']);
        }
        if ($ok) {
            $ok = $this->updateAuthItem();
        } else {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Permission or role') . ' "' . $this->authorization['name'] . '" ' . BaseAmosModule::t('amoscore', 'does not exist. Update is not possible.'));
        }
        return $ok;
    }
    
    /**
     * This method revert the authorization update.
     * @return bool
     */
    public function revertUpdateAuthorization()
    {
        $ok = $this->checkArrayStructure();
//        if ($ok) {
//            MigrationCommon::printConsoleMessage("Struttura ok");
//        }
//        die();
        if ($ok) {
            $ok = $this->instanceAuthItem($this->authorization['name']);
        }
        if ($ok) {
            $ok = $this->revertUpdateAuthItem();
        } else {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Permission or role') . ' "' . $this->authorization['name'] . '" ' . BaseAmosModule::t('amoscore', 'does not exist. Revert is not possible.'));
        }
        return $ok;
    }
    
    /**
     * This method update the authorization.
     * @return bool
     */
    public function updateAuthorizationChildren()
    {
        $ok = $this->checkArrayStructure();
//        if ($ok) {
//            MigrationCommon::printConsoleMessage("Struttura ok");
//        }
//        die();
        if ($ok) {
            $ok = $this->instanceAuthItem($this->authorization['name']);
        }
        if ($ok) {
//            $ok = $this->updateItemChildren();
            $ok = $this->manageItemChildren('update');
        } else {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Permission or role') . ' "' . $this->authorization['name'] . '" ' . BaseAmosModule::t('amoscore', 'does not exist. Update is not possible.'));
        }
        return $ok;
    }
    
    /**
     * This method revert the authorization update.
     * @return bool
     */
    public function revertUpdateAuthorizationChildren()
    {
        $ok = $this->checkArrayStructure();
//        if ($ok) {
//            MigrationCommon::printConsoleMessage("Struttura ok");
//        }
//        die();
        if ($ok) {
            $ok = $this->instanceAuthItem($this->authorization['name']);
        }
        if ($ok) {
//            $ok = $this->revertUpdateItemChildren();
            $ok = $this->manageItemChildren('revert');
        } else {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Permission or role') . ' "' . $this->authorization['name'] . '" ' . BaseAmosModule::t('amoscore', 'does not exist. Revert is not possible.'));
        }
        return $ok;
    }
    
    /**
     * @return bool
     */
    private function updateAuthItem()
    {
        $goOn = $this->checkGoOnUpdateOrRevert($this->authorization['newValues']);
        if (!$goOn) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Auth item not to update') . ": '" . $this->authorization['name'] . "'");
            return true;
        }
        
        $ok = false;
        $authItemName = $this->authorization['name'];
        $newValues = $this->authorization['newValues'];
        $message = BaseAmosModule::t('amoscore', 'Error occurred while updating the auth item') . " '$authItemName'.";
        
        $this->setAuthItemFields($newValues);
        
        if (isset($newValues['ruleName'])) {
            $ruleName = $this->createRule($newValues);
            $this->authItem->ruleName = $ruleName;
        }
        
        try {
            $ok = $this->authManager->update($authItemName, $this->authItem);
            if ($ok) {
                $authItemTypeStr = BaseAmosModule::t('amoscore', 'Permission');
                if ($this->authItem->type == Permission::TYPE_ROLE) {
                    $authItemTypeStr = BaseAmosModule::t('amoscore', 'Role');
                }
                $message = $authItemTypeStr . BaseAmosModule::t('amoscore', ' successfully updated') . ": '$authItemName'";
            }
            MigrationCommon::printConsoleMessage($message);
        } catch (\Exception $exception) {
            MigrationCommon::printConsoleMessage($message);
            MigrationCommon::printConsoleMessage($exception->getMessage());
        }
        
        return $ok;
    }
    
    /**
     * @return bool
     */
    private function revertUpdateAuthItem()
    {
        // Skip revert auth item modify if oldValues is not present.
        if (!isset($this->authorization['oldValues'])) {
            return true;
        }
        
        $goOn = $this->checkGoOnUpdateOrRevert($this->authorization['oldValues']);
        if (!$goOn) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Auth item not to revert') . " '" . $this->authorization['name'] . "'");
            return true;
        }
        
        $ok = false;
        $authItemName = $this->authorization['name'];
        $oldValues = $this->authorization['oldValues'];
        $message = BaseAmosModule::t('amoscore', 'Error occurred while reverting the update of the auth item') . " '$authItemName'.";
        
        $this->setAuthItemFields($oldValues);
        
        $oldValuesKeys = array_keys($oldValues);
        if (in_array('ruleName', $oldValuesKeys)) {
            $ruleName = null;
            if (!is_null($oldValues['ruleName'])) {
                $ruleName = $this->createRule($oldValues);
            }
            $this->authItem->ruleName = $ruleName;
        }
        
        try {
            $ok = $this->authManager->update($authItemName, $this->authItem);
            if ($ok) {
                $authItemTypeStr = BaseAmosModule::t('amoscore', 'Revert update of ');
                if ($this->authItem->type == Permission::TYPE_PERMISSION) {
                    $authItemTypeStr .= BaseAmosModule::t('amoscore', 'permission');
                } elseif ($this->authItem->type == Permission::TYPE_ROLE) {
                    $authItemTypeStr .= BaseAmosModule::t('amoscore', 'role');
                }
                $message = $authItemTypeStr . BaseAmosModule::t('amoscore', ' successfully executed') . ": '$authItemName'";
            }
            MigrationCommon::printConsoleMessage($message);
        } catch (\Exception $exception) {
            MigrationCommon::printConsoleMessage($message);
            MigrationCommon::printConsoleMessage($exception->getMessage());
        }
        
        return $ok;
    }
    
    /**
     * @param array $authItemValues
     * @return bool
     */
    private function checkGoOnUpdateOrRevert($authItemValues)
    {
        $goOn = false;
        foreach ($authItemValues as $fieldName => $fieldValue) {
            if (in_array($fieldName, $this->authItemSettableFields)) {
                $goOn = true;
            }
        }
        return $goOn;
    }
    
    /**
     * This method updates the associations between permissions and roles.
     * @param string $mode It can be "update" or "revert"
     * @return bool Returns true if everything goes well. False otherwise.
     */
    private function manageItemChildren($mode)
    {
        $newValues = $this->authorization['newValues'];
        $addStr = 'add';
        $removeStr = 'remove';
        if ($mode == 'revert') {
            $addStr = 'remove';
            $removeStr = 'add';
        }
        
        if (isset($newValues['addParents'])) {
            $addParents = $newValues['addParents'];
            if (!is_array($addParents)) {
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "The 'addParents' key is not an array. Skipping parents of") . ' ' . $addStr);
                return false;
            }
            switch ($mode) {
                case 'update':
                    $this->addParents($addParents);
                    break;
                case 'revert':
                    $this->removeParents($addParents);
                    break;
            }
        }
        
        if (isset($newValues['removeParents'])) {
            $removeParents = $newValues['removeParents'];
            if (!is_array($removeParents)) {
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "The 'removeParents' key is not an array. Skipping parents of") . ' ' . $removeStr);
                return false;
            }
            switch ($mode) {
                case 'update':
                    $this->removeParents($removeParents);
                    break;
                case 'revert':
                    $this->addParents($removeParents);
                    break;
            }
        }
        
        return true;
    }

//    /**
//     * This method updates the associations between permissions and roles.
//     * @param string $mode It can be "update" or "revert"
//     * @return bool Returns true if everything goes well. False otherwise.
//     */
//    private function updateItemChildren()
//    {
//        $newValues = $this->authorization['newValues'];
//
//        // Adding new parents
//        if (isset($newValues['addParents'])) {
//            $addParents = $newValues['addParents'];
//            if (!is_array($addParents)) {
//                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "The 'addParents' key is not an array. Skipping add parents..."));
//                return false;
//            }
//            $this->addParents($addParents);
//        }
//
//        // Remove old parents
//        if (isset($newValues['removeParents'])) {
//            $removeParents = $newValues['removeParents'];
//            if (!is_array($removeParents)) {
//                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "The 'removeParents' key is not an array. Skipping remove parents..."));
//                return false;
//            }
//            $this->removeParents($removeParents);
//        }
//
//        return true;
//    }
//
//    /**
//     * This method updates the associations between permissions and roles.
//     *
//     * @return bool Returns true if everything goes well. False otherwise.
//     */
//    private function revertUpdateItemChildren()
//    {
//        $newValues = $this->authorization['newValues'];
//
//        // Revert add new parents
//        if (isset($newValues['addParents'])) {
//            $addParents = $newValues['addParents'];
//            if (!is_array($addParents)) {
//                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "The 'addParents' key is not an array. Skipping revert add parents..."));
//                return false;
//            } else {
//                $this->removeParents($addParents);
//            }
//        }
//
//        // Revert remove old parents
//        if (isset($newValues['removeParents'])) {
//            $removeParents = $newValues['removeParents'];
//            if (!is_array($removeParents)) {
//                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "The 'removeParents' key is not an array. Skipping remove parents..."));
//                return false;
//            } else {
//                $this->addParents($removeParents);
//            }
//        }
//
//        return true;
//    }
    
    /**
     * @param array $parentsToAdd
     */
    private function addParents($parentsToAdd)
    {
        foreach ($parentsToAdd as $parentName) {
            if (!is_string($parentName)) {
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'The parent to add is not a string') . ": '" . $parentName . "'. " . BaseAmosModule::t('amoscore', ". Skipping..."));
                continue;
            }
            
            $parent = $this->authManager->getRole($parentName);
            if (is_null($parent)) {
                $parent = $this->authManager->getPermission($parentName);
            }
            if (!is_null($parent)) {
                $messagePrefix = BaseAmosModule::t('amoscore', 'Associations between') . " '" . $parentName . "' " . BaseAmosModule::t('amoscore', 'and') . " '" . $this->authItem->name . "' ";
                $exists = $this->authManager->hasChild($parent, $this->authItem);
                if (!$exists) {
                    $ok = $this->authManager->addChild($parent, $this->authItem);
                    if ($ok) {
                        MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', 'successfully created'));
                    } else {
                        MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', 'failed'));
                    }
                } else {
                    MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', 'exists. Skipping add...'));
                }
            } else {
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Parent not found') . ": '" . $parentName . "'. " . BaseAmosModule::t('amoscore', 'Skipping add...'));
            }
        }
    }
    
    /**
     * This method destroy the associations between permissions and roles.
     * @param array $parentsToRemove
     */
    private function removeParents($parentsToRemove)
    {
        foreach ($parentsToRemove as $parentName) {
            if (!is_string($parentName)) {
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'The parent to remove is not a string') . ": '" . $parentName . "'. " . BaseAmosModule::t('amoscore', '. Skipping...'));
                continue;
            }
            
            $parent = $this->authManager->getRole($parentName);
            if (is_null($parent)) {
                $parent = $this->authManager->getPermission($parentName);
            }
            if (!is_null($parent)) {
                $exists = $this->authManager->hasChild($parent, $this->authItem);
                $messagePrefix = BaseAmosModule::t('amoscore', 'Associations between') . " '" . $parentName . "' " . BaseAmosModule::t('amoscore', 'and') . " '" . $this->authItem->name . "' ";
                if ($exists) {
                    $ok = $this->authManager->removeChild($parent, $this->authItem);
                    if ($ok) {
                        MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', 'successfully removed'));
                    } else {
                        MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', 'remove failed'));
                    }
                } else {
                    MigrationCommon::printConsoleMessage($messagePrefix . BaseAmosModule::t('amoscore', 'not exist. Skipping remove...'));
                }
            } else {
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Parent not found') . ": '" . $parentName . "'. " . BaseAmosModule::t('amoscore', 'Skipping remove...'));
            }
        }
    }
    
    /**
     * @param array $authorization
     */
    private function setAuthItemFields($authorization)
    {
        foreach ($authorization as $fieldName => $fieldValue) {
            if (in_array($fieldName, $this->authItemAutoSettableFields)) {
                $this->authItem->{$fieldName} = $fieldValue;
            }
        }
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
            $ruleClassName = ((substr($authorization['ruleName'], 0, 1) != '\\') ? '\\' : '') . $authorization['ruleName'];
            $ruleTmp = new $ruleClassName;
            $rule = $this->authManager->getRule($ruleTmp->name);
            if (is_null($rule)) {
                $rule = new $ruleClassName;
                $ok = $this->authManager->add($rule);
                if ($ok) {
                    $ruleName = $rule->name;
                    MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Rule successfully created') . ": '$ruleName'");
                } else {
                    MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', "Error during the creation of the rule") . '\'' .$ruleName . '\'');
                }
            } else {
                $ruleName = $rule->name;
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'The rule already exists') . ": '$ruleName'");
            }
        }
        return $ruleName;
    }
}
