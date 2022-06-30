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

use open20\amos\core\exceptions\AmosException;
use open20\amos\core\module\BaseAmosModule;
use yii\helpers\Html;

/**
 * Class ButtonUtility
 * @package open20\amos\core\utilities
 */
class ButtonUtility
{

    /**
     * This method create the buttons array. It contains the strings of html "a" tag ready to print in view.
     * @return array
     */
    public static function composeContextMenuButtons($model = null, $actionModify = null, $actionDelete = null,
                                                     $labelModify = null, $labelDelete = null,
                                                     $labelDeleteConfirm = null, $labelConfirmModify = null)
    {
        $buttons = [];
        if (!empty($actionModify)) {
            if (empty($labelModify)) {
                $labelModify = BaseAmosModule::t('amoscore', 'Modifica');
            }
            $optionsModify = [
                'title' => $labelModify,
            ];

            if (!empty($labelConfirmModify)) {
                $optionsModify ['data'] = [
                    'confirm' => $labelConfirmModify,
                    'method' => 'post',
                    'pjax' => 0
                ];
            }
            if (self::havePermission($model, $actionModify)) {
                $optionsModifyFin = "";
                foreach ($optionsModify as $k => $v) {
                    $optionsModifyFin .= ' '.$k.'="'.$v.'" ';
                }
                $buttons[] = [
                    'label' => $labelModify,
                    'url' => $actionModify,
                    'options' => $optionsModifyFin
                ];
            }
        }

        if (!empty($actionDelete)) {
            if (empty($labelDelete)) {
                $labelDelete = BaseAmosModule::t('amoscore', 'Cancella');
            }

            $labelDeleteConfirm = (!empty($labelDeleteConfirm) ? BaseAmosModule::t('amoscore', $labelDeleteConfirm) : BaseAmosModule::t('amoscore',
                    'Sei sicuro di voler eliminare questo elemento?'));

            $optionsDelete = [
                'title' => $labelDelete,
                'data-confirm' => $labelDeleteConfirm,
                'data-method' => 'post',
                'data-pjax' => 0,
            ];
            if (self::havePermission($model, $actionDelete)) {
                $optionsDeleteFin = "";
                foreach ($optionsDelete as $k2 => $v2) {
                    $optionsDeleteFin .= ' '.$k2.'="'.$v2.'" ';
                }
                $buttons[] = [
                    'label' => $labelDelete,
                    'url' => $actionDelete,
                    'options' => $optionsDeleteFin
                ];
            }
        }

//            if(!empty($this->modelValidatePermission) && $model instanceof WorkflowModelInterface){
//                $optionsModify = ModalUtility::getBackToEditPopup($model, $this->modelValidatePermission, $this->getActionModify(), $optionsModify);
//            }

        return $buttons;
    }

    public static function havePermission($model, $action)
    {
        try {
            $url        = parse_url($action);
            $actionName = \yii\helpers\StringHelper::baseName($url['path']);
            $perm       = \open20\amos\core\helpers\PermissionHelper::checkPermissionModelByUser($model,
                    $actionName);
            return $perm;
        } catch (\Exception $ex) {
            return false;
        }
    }
}