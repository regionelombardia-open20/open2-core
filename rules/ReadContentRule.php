<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\rules
 * @category   CategoryName
 */

namespace open20\amos\core\rules;

use open20\amos\core\interfaces\WorkflowModelInterface;
use open20\amos\cwh\models\CwhNodi;

/**
 * Class ReadContentRule
 * @package open20\amos\core\rules
 */
class ReadContentRule extends BasicContentRule
{
    /**
     * @inheritdoc
     */
    public $name = 'readContent';

    public function ruleLogic($user, $item, $params, $model)
    {

        if($model->isNewRecord){
            return true;
        }
        //check if user is creator, admin, or content validator
        $loggedUser = \Yii::$app->user;
        if ($model->created_by == $loggedUser->id || $loggedUser->can('ADMIN') || $loggedUser->can($model->formName() . 'Validate',
                ['model' => $model])
        ) {
            return true;
        }

        $cwhModule = \Yii::$app->getModule('cwh');
        $cwhEnabled = $model->isEnabledCwh($cwhModule);

        //exclude draft contents when the content has a workflow validated status
        if (!$cwhEnabled) {
            if (!$model->hasAttribute('status')) {
                return true;
            } else {
                $statusAttribute = 'status';
            }
        } else {
            $statusAttribute = $cwhModule->validateOnStatus[get_class($model)]['attribute'];
        }
        if ($model instanceof WorkflowModelInterface) {
            if (!in_array($model->{$statusAttribute}, $model->getCwhValidationStatuses())) {
                return false;
            }
        }

        //check if the content is not enabled in cwh, cwh module is not active or content has been published outside networks (for all users)
        if (!$cwhEnabled || empty($model->destinatari)) {
            return true;
        }

        //the content has been published in a network: check if the network contents are always visible or if user belong to the network
        foreach ($model->destinatari as $cwhNodeId){
            $cwhNode = CwhNodi::findOne($cwhNodeId);
            if($cwhNode && $cwhNode->visibility){
                return true;
            }else{
                if($cwhNode->network->isNetworkUser(null, $user)){
                    return true;
                }
            }
        }
        return false;
    }

}
