<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\utilities
 * @category   CategoryName
 */

namespace open20\amos\core\utilities;

use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\rbac\DbManagerCached;
use open20\amos\dashboard\utility\DashboardUtility;
use yii\log\Logger;

/**
 * Class RbacUtility
 * @package open20\amos\core\utilities
 */
class RbacUtility
{
    /**
     * This method assigns a specified role to ad user.
     * @param int $userId
     * @param string $roleName
     * @param bool $dontResetCache
     * @return bool
     */
    public static function assignRoleToUser($userId, $roleName, $dontResetCache = false)
    {
        /** @var DbManagerCached $authManager */
        $authManager = \Yii::$app->authManager;
        $rolesByUser = $authManager->getRolesByUser($userId);
        if (!in_array($roleName, array_keys($rolesByUser))) {
            $roleObj = $authManager->getRole($roleName);
            if (is_null($roleObj)) {
                \Yii::getLogger()->log(BaseAmosModule::t('amoscore', '#rbac_utility_role_not_found', ['roleName' => $roleName]), Logger::LEVEL_ERROR);
                return false;
            }
            try {
                $authManager->assign($roleObj, $userId);
            } catch (\Exception $exception) {
                \Yii::getLogger()->log(
                    BaseAmosModule::t('amoscore', '#rbac_utility_error_assign_role_to_user', [
                        'roleName' => $roleName,
                        'userId' => $userId
                    ]) . "\n" .
                    $exception->getMessage() . ':' . $exception->getLine() . "\n" . $exception->getMessage(),
                    Logger::LEVEL_ERROR
                );
                return false;
            }
            if (!$dontResetCache) {
                self::resetDashboardAndRbacCache($userId);
            }
        }
        return true;
    }
    
    /**
     * This method revoke the specified role from an user.
     * @param int $userId
     * @param string $roleName
     * @return bool
     */
    public static function revokeRoleFromUser($userId, $roleName, $dontResetCache = false)
    {
        /** @var DbManagerCached $authManager */
        $authManager = \Yii::$app->authManager;
        $rolesByUser = $authManager->getRolesByUser($userId);
        if (in_array($roleName, array_keys($rolesByUser))) {
            $roleObj = $authManager->getRole($roleName);
            if (is_null($roleObj)) {
                \Yii::getLogger()->log(BaseAmosModule::t('amoscore', '#rbac_utility_role_not_found', ['roleName' => $roleName]), Logger::LEVEL_ERROR);
                return false;
            }
            try {
                $ok = $authManager->revoke($roleObj, $userId);
            } catch (\Exception $exception) {
                \Yii::getLogger()->log(
                    BaseAmosModule::t('amoscore', '#rbac_utility_error_revoke_role_from_user', [
                        'roleName' => $roleName,
                        'userId' => $userId
                    ]) . "\n" .
                    $exception->getMessage() . ':' . $exception->getLine() . "\n" . $exception->getMessage(),
                    Logger::LEVEL_ERROR
                );
                return false;
            }
            if ($ok && !$dontResetCache) {
                self::resetDashboardAndRbacCache($userId);
            } elseif (!$ok) {
                \Yii::getLogger()->log(
                    BaseAmosModule::t('amoscore', '#rbac_utility_error_revoke_role_from_user', [
                        'roleName' => $roleName,
                        'userId' => $userId
                    ]),
                    Logger::LEVEL_ERROR
                );
            }
            return $ok;
        }
        return true;
    }
    
    /**
     * This method reset the provided user dashboards and delete the rbac cache.
     * @param int $userId
     */
    public static function resetDashboardAndRbacCache($userId, $onlyDashboard = false)
    {
        DashboardUtility::resetDashboardsByUser($userId);
        if (!$onlyDashboard) {
            \Yii::$app->authManager->deleteAllCache();
        }
    }
}
