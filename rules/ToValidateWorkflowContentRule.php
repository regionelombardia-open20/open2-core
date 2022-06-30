<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\projectmanagement\rules\workflow
 * @category   CategoryName
 */

namespace open20\amos\core\rules;

use open20\amos\core\rules\BasicContentRule;
use open20\amos\projectmanagement\models\ProjectsActivities;

class ToValidateWorkflowContentRule extends BasicContentRule {

    public $name = 'toValidateWorkflowContent';
    public $validateRuleName = '';

    public function ruleLogic($user, $item, $params, $model) {
                //if you have the permission to validate a news and you are in Draft, you cannot send the publish request
        if(!empty($model)){
           if(\Yii::$app->user->can($this->validateRuleName, ['model' => $model])){
               return false;
           }
        }
        return true;
    }

}
