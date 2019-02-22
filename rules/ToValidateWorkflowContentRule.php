<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\projectmanagement\rules\workflow
 * @category   CategoryName
 */

namespace lispa\amos\core\rules;

use lispa\amos\core\rules\BasicContentRule;
use lispa\amos\projectmanagement\models\ProjectsActivities;

class ToValidateWorkflowContentRule extends BasicContentRule
{

    public $name = 'toValidateWorkflowContent';
    public $validateRuleName = '';

    public function ruleLogic($user, $item, $params, $model)
    {
        //if you have the permission to validate a news and you are in Draft, you cannot send the publish request
        if(!empty($model)){
           if(\Yii::$app->user->can($this->validateRuleName, ['model' => $model])){
               return false;
           }
        }

        return true;
    }
}