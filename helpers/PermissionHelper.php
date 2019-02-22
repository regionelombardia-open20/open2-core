<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\helpers
 * @category   CategoryName
 */

namespace lispa\amos\core\helpers;

/**
 * Class PermissionHelper
 * Generic functionality of use for permission management
 * @package lispa\amos\core\helpers
 */
class PermissionHelper
{
    /**
     * Map of the shares, this array describes specific actions in Actions attributable to Permission
     * @var array
     */
    private static $exceptionActionMap = [
        'VIEW' => 'READ',
        'INDEX' => 'READ',
        'UPDATE-PROFILE' => 'UPDATE',
    ];

    /**
     * @param $modelName
     * @param $action
     * Depending on the model name and the action passed it will check that it exists between system permits.
     * if this permission exists is returned the string rappresent the permission, else, that the action is
     * an "exception action". if so string represents the permission is returned
     * if no permission match return null
     * @return null|string - NULL is for no permission find - STRING reppresent the permission find
     */
    public static function findPermissionModelAction($modelName, $action)
    {
        $permission = strtoupper($modelName) . '_' . strtoupper($action);
        $permissionsAvaiable = \Yii::$app->authManager->getPermissions();
        if (isset($permissionsAvaiable[$permission])) {
            return $permission;
        } else {
            if (isset(self::$exceptionActionMap[strtoupper($action)])) {
                return strtoupper($modelName) . '_' . self::$exceptionActionMap[strtoupper($action)];
            } else {
                return null;
            }
            $permission = strtoupper($modelName) . '-' . strtoupper($action);
        }
    }

}