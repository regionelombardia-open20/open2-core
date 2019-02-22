<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\rules
 * @category   CategoryName
 */

namespace lispa\amos\core\rules;


class UserValidatorContentOnDomain extends DefaultOwnContentRule
{
    /**
     * @inheritdoc
     */
    public $name = 'UserValidatorContentOnDomain';

    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        $modelClassName = '';
        $cwhModule = \Yii::$app->getModule('cwh');
        if (isset($params['model'])) {
            /** @var Record $model */
            $model = $params['model'];
            $modelClassName = $model->className();

            if (!$model->id) {
                $post = \Yii::$app->getRequest()->post();
                $get = \Yii::$app->getRequest()->get();
                if (isset($get['id'])) {
                    $model = $this->instanceModel($model, $get['id']);
                } elseif (isset($post['id'])) {
                    $model = $this->instanceModel($model, $post['id']);
                }
            }
        } else {
            $controller = \Yii::$app->controller;
            if ($controller instanceof BaseController) {
                $modelClassName = $controller->getModelClassName();
            }
        }
        if (!isset($cwhModule) || !in_array($modelClassName, $cwhModule->modelsEnabled)) {
            return false;
        } else {
            $scope = $cwhModule->getCwhScope();
            if (empty($scope)) {
                return false;
            }
            $permissionCwhValidate = $cwhModule->permissionPrefix . "_VALIDATE_" . $modelClassName;
            $networkKeys = array_keys($scope);
            $allow = true;
            foreach ($networkKeys as $networkKey) {
                $networkConfig = \lispa\amos\cwh\models\CwhConfig::findOne(['tablename' => $networkKey]);
                if (is_null($networkConfig)) {
                    $allow = false;
                } else {
                    $networkId = $scope[$networkKey];
                    $allow = $allow && $this->userCreatorContentPermission($user, $permissionCwhValidate,
                            $networkConfig->id, $networkId);
                }
            }
            return $allow;
        }
    }

    /**
     * @param int $userId
     * @param string $permissionCwhValidate
     * @param int $networkConfigId - id of network configuration (cwh_config table)
     * @param int $networkId - id of the network (eg. community, organization user is working within)
     * @return bool
     */
    private function userCreatorContentPermission($userId, $permissionCwhValidate, $networkConfigId, $networkId)
    {

        $cwhContentValidatePerssions = \lispa\amos\cwh\models\base\CwhAuthAssignment::findOne([
            'user_id' => $userId,
            'item_name' => $permissionCwhValidate,
            'cwh_config_id' => $networkConfigId,
            'cwh_network_id' => $networkId
        ]);
        return (!is_null($cwhContentValidatePerssions));
    }
}