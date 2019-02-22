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

use lispa\amos\core\record\Record;
use Yii;

/**
 * Class ValidatorUpdateContentRule
 * @package lispa\amos\core\rules
 */
class ValidatorUpdateContentRule extends DefaultOwnContentRule
{
    /**
     * @inheritdoc
     */
    public $name = 'validatorUpdateContent';

    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        if (isset($params['model'])) {
            /** @var Record $model */
            $model = $params['model'];
            $modelClassName = $model->className();
            $cwhModule = Yii::$app->getModule('cwh');

            if (!$model->id) {
                $post = \Yii::$app->getRequest()->post();
                $get = \Yii::$app->getRequest()->get();
                if (isset($get['id'])) {
                    $model = $this->instanceModel($model, $get['id']);
                } elseif (isset($post['id'])) {
                    $model = $this->instanceModel($model, $post['id']);
                }
            }
            if (!isset($cwhModule) || !in_array($modelClassName, $cwhModule->modelsEnabled)) {
                return true;
            } else {
                return $this->validatorContentUpdatePermission($model);
            }
        } else {
            return false;
        }
    }

    /**
     * @param Record $model
     * @return bool
     */
    private function validatorContentUpdatePermission($model)
    {
        // at the creation of a model, VALIDATORS, ADMIN and Community managers can publish directly a news
        // if you create a content using hidecwhtab, the model is creaed without a validator, so you cannot do the normal check for validation permission
        $cwhModule = \Yii::$app->getModule('cwh');
        $cwhEnabled = (isset($cwhModule) && in_array(get_class($model), $cwhModule->modelsEnabled) && $cwhModule->behaviors);
        if($model->isNewRecord || ($cwhEnabled && empty($model->validatori))) {
            $scope = $cwhModule->getCwhScope();
            if (isset($cwhModule) && !empty($scope)) {
                $scope = $cwhModule->getCwhScope();
                if (isset($scope['community'])) {
                    $community = \lispa\amos\community\models\Community::findOne($scope['community']);
                    if (\lispa\amos\community\utilities\CommunityUtil::hasRole($community)) {
                        return true;
                    }
                }
            }
            // if(empty($scope)&&  \Yii::$app->user->can('FACILITATOR') ){ // OLD FIXED GENERIC FACILITATOR PERMISSION
            if(empty($scope)&&  \Yii::$app->user->can($model->getFacilitatorRole()) ){
                return true;
            }

            $validatorRole = $model->getValidatorRole();
            if(\Yii::$app->user->can('VALIDATOR') || \Yii::$app->user->can($validatorRole) ){
                return true;
            }
        }
        
        $cwhActiveQuery = new \lispa\amos\cwh\query\CwhActiveQuery(
            $model->className(), [
            'queryBase' => $model::find()->distinct()
        ]);
        $queryToValidateIds = $cwhActiveQuery->getQueryCwhToValidate(false)->select($model::tableName().'.id')->column();
        return (in_array($model->id, $queryToValidateIds));
    }
}
