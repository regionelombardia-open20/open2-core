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

use lispa\amos\core\controllers\BaseController;
use lispa\amos\core\record\Record;


/**
 * Class UserValidatorContentRule
 * @package lispa\amos\core\rules
 */
class UserValidatorContentRule extends DefaultOwnContentRule
{
    /**
     * @inheritdoc
     */
    public $name = 'userValidatorContent';

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

        }else {
            $controller = \Yii::$app->controller;
            if($controller instanceof BaseController)
            {
                $modelClassName = $controller->getModelClassName();
            }
        }
        if (!isset($cwhModule) || !in_array($modelClassName, $cwhModule->modelsEnabled)) {
            return false;
        } else {
            $permissionCwhValidate = $cwhModule->permissionPrefix. "_VALIDATE_".$modelClassName;
            return $this->userValidatorContentPermission($user, $permissionCwhValidate);
        }
    }

    /**
     * @param int $userId
     * @param string $permissionCwhValidate
     * @return bool
     */
    private function userValidatorContentPermission($userId ,$permissionCwhValidate)
    {
        $cwhContentValidatePerssions = \lispa\amos\cwh\models\base\CwhAuthAssignment::find()->andWhere(['user_id' => $userId, 'item_name' => $permissionCwhValidate])->all();
        return (!empty($cwhContentValidatePerssions));
    }
}