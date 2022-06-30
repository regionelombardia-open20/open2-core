<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\workflow
 * @category   CategoryName
 */

/**
 * TODO
 * Questa classe è da spostare altrove in un posto più adatto. Dev'essere generica e ad esempio in un
 * componente da mettere nella vendor.
 */

namespace open20\amos\core\workflow;

use cornernote\workflow\manager\components\WorkflowDbSource;
use open20\amos\core\controllers\BaseController;
use open20\amos\core\record\Record;
use raoul2000\workflow\base\Transition;
use Yii;

/**
 * Class ContentDefaultWorkflowDbSource
 * @package open20\amos\core\workflow
 */
class ContentDefaultWorkflowDbSource extends WorkflowDbSource
{
    /**
     * Filter transitions based on user permissions
     *
     * @param mixed $statusId
     * @param null $model
     * @return array
     */
    public function getTransitions($statusId, $model = null)
    {
        $transitions = parent::getTransitions($statusId, $model);
        $transitionsAllowed = [];
        $gotModel = false;
        $user = null;
        $isConsoleApplication = false;
        if (Yii::$app instanceof \yii\web\Application) {
            $user = Yii::$app->user;
        } elseif (Yii::$app instanceof \yii\console\Application) {
            $isConsoleApplication = true;
        }
        if (isset($model) && $model instanceof Record) {
            $gotModel = true;
        } else {
            $controller = Yii::$app->controller;
            if (isset($controller) && $controller instanceof BaseController) {
                $model = $controller->model;
                if (isset($model) && $model instanceof Record) {
                    $gotModel = true;
                }
            }
        }
        /** @var Transition $transition */
        foreach ($transitions as $transition) {
            if ($isConsoleApplication) {
                $transitionsAllowed[] = $transition;
            } else {
                if ($gotModel) {
                    if ($user->can($transition->getEndStatus()->getId(), ['model' => $model])) {
                        $transitionsAllowed[] = $transition;
                    }
                } else {
                    if ($user->can($transition->getEndStatus()->getId())) {
                        $transitionsAllowed[] = $transition;
                    }
                }
            }
        }
        return $transitionsAllowed;
    }
}
