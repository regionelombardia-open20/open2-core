<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\utilities
 * @category   CategoryName
 */

namespace lispa\amos\core\utilities;

use lispa\amos\core\helpers\Html;
use lispa\amos\core\interfaces\WorkflowModelInterface;
use lispa\amos\core\module\BaseAmosModule;
use lispa\amos\core\record\Record;
use Yii;
use yii\base\BaseObject;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;

/**
 * Class ModalUtility
 * @package lispa\amos\core\utilities
 */
class ModalUtility extends BaseObject
{
    /**
     * When user clicks on edit button:
     * - if the user can validate the model or the model is in draft status goes to update page, the status will not be changed
     * - otherwise a popup tells the user the status will be set back to draft, on confirmation the status changes and user goes to update page
     *
     * @param WorkflowModelInterface|Record $model
     * @param string $modelValidatePermission
     * @param $actionModify
     * @param $optionsModify
     * @return array $optionsModify
     */
    public static function getBackToEditPopup($model, $modelValidatePermission, $actionModify, $optionsModify)
    {
        if (!(isset(Yii::$app->params['hideWorkflowTransitionWidget']) && Yii::$app->params['hideWorkflowTransitionWidget'])) {
            $editStatus = $model->getDraftStatus();
            if ($model->status != $editStatus && !\Yii::$app->user->can($modelValidatePermission,
                    ['model' => $model]) && $model->created_by == \Yii::$app->user->id
            ) {
                $modelWorkflow = $model->getWorkflowSource();
                $editStatusLabel = $modelWorkflow->getStatus($editStatus)->label;
                $editStatusLabel = $editStatusLabel;

                Modal::begin(['id' => 'backToEditStatusPopup-' . $model->id]);
                echo Html::tag('div',
                    BaseAmosModule::tHtml('amoscore', '#back_to_edit_popup_part1') . ' ' . $editStatusLabel .
                    BaseAmosModule::tHtml('amoscore', '#back_to_edit_popup_part2')
                );
                echo Html::tag('div',
                    Html::a(BaseAmosModule::tHtml('amoscore', '#cancel'), null,
                        ['class' => 'btn btn-secondary', 'data-dismiss' => 'modal'])
                    . Html::a(BaseAmosModule::tHtml('amoscore', '#continue'), [$actionModify . '&backToEditStatus=1'],
                        ['class' => 'btn btn-navigation-primary']),
                    ['class' => 'pull-right m-15-0']
                );
                Modal::end();

                $optionsModify = ArrayHelper::merge($optionsModify,
                    ['data-toggle' => 'modal', 'data-target' => '#backToEditStatusPopup-' . $model->id]);
            }
        }
        return $optionsModify;
    }

    public static function createConfirmModalDefaultId()
    {
        return 'confirm-modal-id';
    }

    /**
     * @param array $modalConfiguration
     */
    public static function createConfirmModal($modalConfiguration)
    {
        // Check all configurations
        $modalId = ((isset($modalConfiguration['id']) && is_string($modalConfiguration['id'])) ?
            $modalConfiguration['id'] :
            self::createConfirmModalDefaultId());
        $modalDescriptionText = ((isset($modalConfiguration['modalDescriptionText']) && is_string($modalConfiguration['modalDescriptionText'])) ?
            $modalConfiguration['modalDescriptionText'] :
            '');
        $cancelLabel = ((isset($modalConfiguration['cancelBtnLabel']) && is_string($modalConfiguration['cancelBtnLabel'])) ?
            $modalConfiguration['cancelBtnLabel'] :
            BaseAmosModule::tHtml('amoscore', '#cancel'));
        $cancelLink = ((isset($modalConfiguration['cancelBtnLink']) && is_string($modalConfiguration['cancelBtnLink'])) ?
            $modalConfiguration['cancelBtnLink'] :
            null);
        $cancelOptions = ((isset($modalConfiguration['cancelBtnOptions']) && is_array($modalConfiguration['cancelBtnOptions'])) ?
            $modalConfiguration['cancelBtnOptions'] :
            ['class' => 'btn btn-secondary', 'data-dismiss' => 'modal']);
        $confirmLabel = ((isset($modalConfiguration['confirmBtnLabel']) && is_string($modalConfiguration['confirmBtnLabel'])) ?
            $modalConfiguration['confirmBtnLabel'] :
            BaseAmosModule::tHtml('amoscore', '#confirm'));
        $confirmLink = ((isset($modalConfiguration['confirmBtnLink']) && is_string($modalConfiguration['confirmBtnLink'])) ?
            $modalConfiguration['confirmBtnLink'] :
            null);
        $confirmOptions = ((isset($modalConfiguration['confirmBtnOptions']) && is_array($modalConfiguration['confirmBtnOptions'])) ?
            $modalConfiguration['confirmBtnOptions'] :
            ['class' => 'btn btn-navigation-primary']);
        $modalOptions = ((isset($modalConfiguration['modalOptions']) && is_array($modalConfiguration['modalOptions'])) ?
            $modalConfiguration['modalOptions'] :
            ['class' => 'pull-right m-15-0']);

        // Buttons
        $buttons = Html::a($cancelLabel, [$cancelLink], $cancelOptions) . Html::a($confirmLabel, [$confirmLink], $confirmOptions);

        // Make the modal
        Modal::begin(['id' => $modalId]);
        echo Html::tag('div', $modalDescriptionText);
        echo Html::tag('div', $buttons, $modalOptions);
        Modal::end();
    }

    /**
     * @param array $params Configurations array
     * @return string
     */
    public static function addConfirmRejectWithModal($configurations)
    {
        $modalConfigurations = [
            'id' => $configurations['modalId'],
            'modalDescriptionText' => $configurations['modalDescriptionText'],
            'confirmBtnLink' => $configurations['btnLink'],
        ];
        $btnOptions = [
            'data-toggle' => 'modal',
            'data-target' => '#' . $configurations['modalId']
        ];
        $skipKeys = array_keys($btnOptions);
        if (isset($configurations['btnOptions']) && is_array($configurations['btnOptions'])) {
            foreach ($configurations['btnOptions'] as $key => $value) {
                // Exclude the data
                if (!in_array($key, $skipKeys)) {
                    $btnOptions[$key] = $value;
                }
            }
        }
        $content = self::createConfirmModal($modalConfigurations);
        $content .= Html::a($configurations['btnText'], $configurations['btnLink'], $btnOptions);
        return $content;
    }

    public static function createAlertModalDefaultId()
    {
        return 'alert-modal-id';
    }

    /**
     * @param array $modalConfiguration
     *
     * eg. [
     *  'id' => 'my-modal-id',
     *  'modalDescriptionText' => 'This is the text I want to display inside the modal alert',
     *  'btnLabel' => 'OK', //if missing 'close' label is used
     *  'btnLink' => '/module/controller/my-action',
     *  'btnOptions' => [ 'class' => 'my-btn-class' ], //options for html tag a - if missing a default class is used and the button just closes the modal
     *  'modalOptions' => ['class' => 'my-div-class'], //option for html tag div containing the button - if missing a default class is used
     * ]
     */
    public static function createAlertModal($modalConfiguration)
    {
        // Check all configurations
        $modalId = ((isset($modalConfiguration['id']) && is_string($modalConfiguration['id'])) ?
            $modalConfiguration['id'] :
            self::createAlertModalDefaultId());
        $modalDescriptionText = ((isset($modalConfiguration['modalDescriptionText']) && is_string($modalConfiguration['modalDescriptionText'])) ?
            $modalConfiguration['modalDescriptionText'] :
            '');
        $btnLabel = ((isset($modalConfiguration['btnLabel']) && is_string($modalConfiguration['btnLabel'])) ?
            $modalConfiguration['btnLabel'] :
            BaseAmosModule::tHtml('amoscore', '#close'));
        $btnLink = ((isset($modalConfiguration['btnLink']) && is_string($modalConfiguration['btnLink'])) ?
            [$modalConfiguration['btnLink']] :
            null);
        $btnOptions = ((isset($modalConfiguration['btnOptions']) && is_array($modalConfiguration['btnOptions'])) ?
            $modalConfiguration['btnOptions'] :
            ['class' => 'btn btn-secondary', 'data-dismiss' => 'modal']);
        $modalOptions = ((isset($modalConfiguration['modalOptions']) && is_array($modalConfiguration['modalOptions'])) ?
            $modalConfiguration['modalOptions'] :
            ['class' => 'pull-right m-15-0']);

        // Buttons
        $buttons = Html::a($btnLabel, $btnLink, $btnOptions);

        // Make the modal
        Modal::begin(['id' => $modalId]);
        echo Html::tag('div', $modalDescriptionText, []);
        echo Html::tag('div', $buttons, $modalOptions);
        Modal::end();
    }

    /**
     * @return string
     */
    public static function amosModalDefaultId()
    {
        return 'amos-modal-id';
    }

    /**
     * @param array $modalConfiguration
     */
    public static function amosModal($modalConfiguration)
    {
        // Check all configurations
        $modalId = ((isset($modalConfiguration['id']) && is_string($modalConfiguration['id'])) ?
            $modalConfiguration['id'] :
            self::amosModalDefaultId());
        $modalHeaderClass = ((isset($modalConfiguration['headerClass']) && is_string($modalConfiguration['headerClass'])) ?
            $modalConfiguration['headerClass'] :
            '');
        $modalHeaderText = ((isset($modalConfiguration['headerText']) && is_string($modalConfiguration['headerText'])) ?
            Html::tag('h3', $modalConfiguration['headerText'], ['class' => 'modal-title']) :
            Html::tag('h3', '', ['class' => 'modal-title']));
        $modalBodyContent = ((isset($modalConfiguration['modalBodyContent']) && is_string($modalConfiguration['modalBodyContent'])) ?
            $modalConfiguration['modalBodyContent'] :
            '');
        $containerOptions = ((isset($modalConfiguration['containerOptions']) && is_array($modalConfiguration['containerOptions'])) ?
            $modalConfiguration['containerOptions'] :
            ['class' => 'modal-utility']);
        $modalClassSize = ((isset($modalConfiguration['modalClassSize']) && is_string($modalConfiguration['modalClassSize'])) ?
            $modalConfiguration['modalClassSize'] :
            '');

        // Make the modal
        Modal::begin([
            'id' => $modalId,
            'header' => $modalHeaderText,
            'headerOptions' => ['class' => $modalHeaderClass],
            'options' => $containerOptions,
            'size' => $modalClassSize,
        ]);
        echo Html::tag('div', $modalBodyContent);
        Modal::end();
    }

}
