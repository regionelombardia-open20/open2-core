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

use Yii;
use yii\db\Migration;
use yii\rbac\Permission;

/**
 * Class AmosMigration
 * @deprecated Don't use this class. Not removed only for retro compatibility.
 * @package open20\amos\core\migration
 */
class AmosMigration extends Migration
{
    /**
     * @var array $authItems An array where you can insert permissions and roles that will be inserted in the database.
     */
    protected $authorizations;

    /**
     * @var array $fieldsToCheck This is internal configurations useful to check the integrity of the array content.
     */
    private $fieldsToCheck = [
        'name' => 'STRING',
        'type' => 'INT',
        'description' => 'STRING',
        'ruleName' => 'STRING'
    ];

    /**
     */
    public function init()
    {
        parent::init();
        $this->db->enableSchemaCache = false;
        $this->setAuthorizations();
        
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
     */
    protected function setAuthorizations()
    {
        $this->authorizations = [];
    }

    /**
     * This is the method that parse all the authorizations array and add them to the system. It checks the data
     * integrity and add permissions, roles and associations between them.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    public function addAuthorizations()
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
    protected function checkArrayStructure()
    {
        foreach ($this->authorizations as $authorization) {
            $keys = array_keys($authorization);
            foreach ($this->fieldsToCheck as $fieldName => $fieldType) {
                if (!in_array($fieldName, $keys)) {
                    echo "Campo '$fieldName' non presente nell'array.\n";
                    print_r($authorization);
                    print_r("\n");
                    return false;
                }
                if (!$this->checkFieldType($fieldName, $fieldType, $authorization)) {
                    echo "Contenuto campo '$fieldName' del tipo errato. Dev'essere $fieldType.\n";
                    print_r($authorization);
                    print_r("\n");
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Method that checks the correct type of a field value.
     *
     * @param string $fieldName Name of an internal array field.
     * @param string $fieldType Value type of an internal array field.
     * @param array $authorization One internal array.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    protected function checkFieldType($fieldName, $fieldType, $authorization)
    {
        switch ($fieldType) {
            case 'STRING':
                if (($fieldName == 'ruleName') && is_null($authorization[$fieldName])) {
                    break;
                }
                if (!is_string($authorization[$fieldName])) {
                    return false;
                }
                break;
            case 'INT':
                if (!is_numeric($authorization[$fieldName])) {
                    return false;
                }
                break;
            default:
                return false;
        }
        return true;
    }

    /**
     * Method that insert all permissions and roles in the authorizations array.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    protected function addPermissionsAndRoles()
    {
        foreach ($this->authorizations as $authorization) {
            if ($authorization['type'] == Permission::TYPE_PERMISSION) {
                $this->createPermission($authorization);
            } elseif ($authorization['type'] == Permission::TYPE_ROLE) {
                $this->createRole($authorization);
            }
        }

        return true;
    }

    /**
     * Method that insert a single permission in the system. It print a message if permission exists or permission
     * is successfully created or an error occurred.
     *
     * @param array $authorization One internal array.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    protected function createPermission($authorization)
    {
        // Rule creation
        $ruleName = $this->createRule($authorization);

        $authManager = Yii::$app->getAuthManager();

        // Verify if permission exists
        $permissionName = $authorization['name'];
        if (!is_null($authManager->getPermission($permissionName))) {
            echo "Permesso '" . $permissionName . "' esistente. Skippo...\n";
            return false;
        }

        // Add permission
        $perm = $authManager->createPermission($permissionName);
        $perm->description = $authorization['description'];
        $perm->ruleName = $ruleName;
        $ok = $authManager->add($perm);

        // Messages to user
        if ($ok) {
            echo "Permesso '$permissionName' creato correttamente.\n";
        } else {
            echo "Errore durante la creazione del permesso '$permissionName'.\n";
        }

        return $ok;
    }

    /**
     * Method that create a rule in the system. It print a message if permission exists or permission
     * is successfully created or an error occurred.
     *
     * @param array $authorization One internal array.
     *
     * @return null|string Returns rule name if succesfully created or exists. Null otherwise.
     */
    protected function createRule($authorization)
    {
        $authManager = Yii::$app->getAuthManager();
        $ruleName = null;
        $rule = null;
        if (is_string($authorization['ruleName'])) {
            $ruleClassName = '\\' . $authorization['ruleName'];
            $ruleTmp = new $ruleClassName;

            $rule = $authManager->getRule($ruleTmp->name);
            if (is_null($rule)) {
                $rule = new $ruleClassName;
                $ok = $authManager->add($rule);
                if ($ok) {
                    $ruleName = $rule->name;
                    echo "Regola '$ruleName' creata correttamente.\n";
                } else {
                    echo "Errore durante la creazione della regola '$ruleName'.\n";
                }
            } else {
                $ruleName = $rule->name;
            }
        }
        return $ruleName;
    }

    /**
     * Method that insert a single role in the system. It print a message if role exists or role is successfully
     * created or an error occurred.
     *
     * @param array $authorization One internal array.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    protected function createRole($authorization)
    {
        // Verify if role exists
        $roleName = $authorization['name'];
        if (!is_null(Yii::$app->getAuthManager()->getRole($roleName))) {
            echo "Ruolo '" . $roleName . "' esistente. Skippo...\n";
            return false;
        }

        // Add new role
        $role = Yii::$app->getAuthManager()->createRole($roleName);
        $role->description = $authorization['description'];
        $role->ruleName = $authorization['ruleName'];
        $ok = Yii::$app->getAuthManager()->add($role);

        if ($ok) {
            echo "Ruolo '$roleName' creato correttamente.\n";
        } else {
            echo "Errore durante la creazione del ruolo '$roleName'.\n";
        }

        return $ok;
    }

    /**
     * This method creates the associations between permissions and roles.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    protected function addItemChilds()
    {
        foreach ($this->authorizations as $authorization) {
            if (isset($authorization['parent']) && is_array($authorization['parent'])) {
                foreach ($authorization['parent'] as $parentStr) {
                    if (!is_string($parentStr)) {
                        echo "Il parent '" . $parentStr . "' non è una stringa";
                        continue;
                    }

                    $parent = Yii::$app->getAuthManager()->getRole($parentStr);
                    if (is_null($parent)) {
                        $parent = Yii::$app->getAuthManager()->getPermission($parentStr);
                    }
                    if (!is_null($parent)) {
                        $child = null;
                        $childStr = $authorization['name'];
                        if ($authorization['type'] == Permission::TYPE_PERMISSION) {
                            $child = Yii::$app->getAuthManager()->getPermission($childStr);

                        } elseif ($authorization['type'] == Permission::TYPE_ROLE) {
                            $child = Yii::$app->getAuthManager()->getRole($childStr);
                        }

                        if (!is_null($child)) {
                            $messagePrefix = "Associazione fra " . $parentStr . " e " . $childStr;
                            $exists = Yii::$app->getAuthManager()->hasChild($parent, $child);
                            if (!$exists) {
                                $ok = Yii::$app->getAuthManager()->addChild($parent, $child);
                                if ($ok) {
                                    echo $messagePrefix . " eseguita correttamente\n";
                                } else {
                                    echo $messagePrefix . " non riuscita\n";
                                }
                            } else {
                                echo $messagePrefix . " esistente. Skippo...\n";
                            }
                        } else {
                            echo "Child '$childStr' nullo\n";
                        }
                    } else {
                        echo "Ruolo parent '$parentStr' nullo\n";
                    }
                }
            }
        }

        return true;
    }

    /**
     * This is the method that parse all the authorizations array and remove them from the system. It checks the data
     * integrity and add permissions, roles and associations between them.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    public function removeAuthorizations()
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
    protected function removeItemChilds()
    {
        foreach ($this->authorizations as $authorization) {
            if (isset($authorization['parent']) && is_array($authorization['parent'])) {
                foreach ($authorization['parent'] as $parentStr) {
                    if (!is_string($parentStr)) {
                        echo "Il parent '" . $parentStr . "' non è una stringa";
                        continue;
                    }

                    $parent = Yii::$app->getAuthManager()->getRole($parentStr);
                    if (is_null($parent)) {
                        $parent = Yii::$app->getAuthManager()->getPermission($parentStr);
                    }
                    if (!is_null($parent)) {
                        $child = null;
                        $childStr = $authorization['name'];
                        if ($authorization['type'] == Permission::TYPE_PERMISSION) {
                            $child = Yii::$app->getAuthManager()->getPermission($childStr);

                        } elseif ($authorization['type'] == Permission::TYPE_ROLE) {
                            $child = Yii::$app->getAuthManager()->getRole($childStr);
                        }

                        if (!is_null($child)) {
                            $messagePrefix = "Rimozione associazione fra " . $parentStr . " e " . $childStr;
                            $exists = Yii::$app->getAuthManager()->hasChild($parent, $child);
                            if ($exists) {
                                $ok = Yii::$app->getAuthManager()->removeChild($parent, $child);
                                if ($ok) {
                                    echo $messagePrefix . " eseguita correttamente\n";
                                } else {
                                    echo $messagePrefix . " non riuscita\n";
                                }
                            } else {
                                echo $messagePrefix . " non esistente. Skippo...\n";
                            }
                        } else {
                            echo "Child '$childStr' nullo\n";
                        }
                    } else {
                        echo "Ruolo parent '$parentStr' nullo\n";
                    }
                }
            }
        }

        return true;
    }

    /**
     * Method that delete all permissions and roles in the authorizations array.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    protected function removePermissionsAndRoles()
    {
        foreach ($this->authorizations as $authorization) {
            if ($authorization['type'] == Permission::TYPE_PERMISSION) {
                $this->deletePermission($authorization);
            } elseif ($authorization['type'] == Permission::TYPE_ROLE) {
                $this->deleteRole($authorization);
            }
        }

        return true;
    }

    /**
     * Method that delete a single permission from the system. It print a message if permission does not exist or
     * permission is successfully deleted or an error occurred.
     *
     * @param array $authorization One internal array.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    protected function deletePermission($authorization)
    {
        // Verify if permission exists
        $permissionName = $authorization['name'];
        $perm = Yii::$app->getAuthManager()->getPermission($permissionName);
        if (is_null($perm)) {
            echo "Permesso '" . $permissionName . "' non esistente. Skippo...\n";
            return false;
        }

        // Remove permission
        $ok = Yii::$app->getAuthManager()->remove($perm);

        // Messages to user
        if ($ok) {
            echo "Permesso '$permissionName' eliminato correttamente.\n";
        } else {
            echo "Errore durante l'eliminazione del permesso '$permissionName'.\n";
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
    protected function deleteRole($authorization)
    {
        // Verify if role exists
        $roleName = $authorization['name'];
        $role = Yii::$app->getAuthManager()->getRole($roleName);
        if (is_null($role)) {
            echo "Ruolo '" . $roleName . "' non esistente. Skippo...\n";
            return false;
        }

        // Remove role
        $ok = Yii::$app->getAuthManager()->remove($role);

        // Messages to user
        if ($ok) {
            echo "Ruolo '$roleName' eliminato correttamente.\n";
        } else {
            echo "Errore durante l'eliminazione del ruolo '$roleName'.\n";
        }

        return $ok;
    }
}