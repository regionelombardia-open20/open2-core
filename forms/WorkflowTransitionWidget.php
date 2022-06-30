<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms
 * @category   CategoryName
 */

namespace open20\amos\core\forms;

use open20\amos\core\controllers\CrudController;
use open20\amos\core\helpers\Html;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\module\AmosModule;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\record\Record;
use kartik\base\Widget;
use kartik\select2\Select2;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\Transition;
use raoul2000\workflow\base\Workflow;
use yii\db\ActiveRecord;

/**
 * Class WorkflowStateWidget
 * Renders the widget useful to change a model workflow status.
 *
 * @package open20\amos\core\forms
 */
class WorkflowTransitionWidget extends Widget
{
    /**
     * @var string $containerWidgetClass
     */
    public $containerWidgetClass = 'col-xs-12 workflow-transition-widget';

    /**
     * @var string $icon to validate
     */
    public $icon = 'refresh-alt';

    /**
     * @var string $icon validate
     */
    public $iconValidate = 'check-all';

    /**
     * @var string $icon edit
     */
    public $iconEdit = 'edit';

    /**
     * @var array Array of the icon options
     */
    public $iconOptions = ['style' => 'font-size: 50px;'];

    /**
     * @var string $buttonLayout
     */
    public $buttonLayout = "<div id=\"workflow-form-actions\" class=\"pull-right\">{buttonSubmit}</div>";

    /**
     * If the initialMessage is not set the default value is 'CURRENT STATE'
     * @var string $initialMessage
     */
    public $initialMessage;

    /**
     * If the label is not set the default value is 'Not set'
     * @var string $initialLabel
     */
    public $initialLabel;

    /**
     * @var string $classHr
     */
    public $classHr = 'workflow';

    /**
     * @var string $classDivIcon
     */
    public $classDivIcon;

    /**
     * @var string $classDivLabel
     */
    public $classDivLabel;

    /**
     * @var string $classDivMessage
     */
    public $classDivMessage;

    /**
     * @var string $classDivButton
     */
    public $classDivButton;

    /**
     * @var string $customJs
     */
    public $customJs;

    /**
     * If it is not set the default value is 'Are you sure you want to change status?'
     * @var string $dataConfirm
     */
    public $dataConfirm;

    /**
     * @var bool $viewWidgetOnNewRecord If true force to view the widget when the model is in new record state
     */
    public $viewWidgetOnNewRecord = false;

    /**
     * @var ActiveRecord $model
     */
    private $model;

    /**
     * @var ActiveForm $form
     */
    private $form;

    /**
     * @var string $workflowId
     */
    private $workflowId;

    /**
     * @var array $metadata
     */
    private $metadata;

    /**
     * @var array $statuses
     */
    private $statuses = [];

    /**
     * @var AmosModule $module
     */
    private $module;

    /**
     * @var string $translationCategory
     */
    private $translationCategory;

    /**
     * @var bool $hideButtons If true hide all change status buttons.
     */
    private $hideButtons = false;

    /**
     * @var bool $seeStatusButtonId If true and the user has SEE_STATUS_BUTTON_ID permission he can view the change status button ID in the button title. Useful for debug.
     */
    private $seeStatusButtonId = false;

    /**
     *
     * Set of the permissionSave
     */
    public function init()
    {
        /** @var CrudController $controller */
        $controller = \Yii::$app->controller;
        $moduleName = $controller->module->uniqueId;
        $this->module = \Yii::$app->getModule($moduleName);
        if (!$this->translationCategory) {
            $this->translationCategory = preg_replace('/[^aA-zZ]/i', '', 'amos' . $moduleName);
        }

        parent::init();
    }

    /**
     * @return string
     */
    public function getWorkflowId()
    {
        return $this->workflowId;
    }

    /**
     * @param string $workflowId
     */
    public function setWorkflowId($workflowId)
    {
        $this->workflowId = $workflowId;
    }

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @return string
     */
    public function getTranslationCategory()
    {
        return $this->translationCategory;
    }

    /**
     * @param string $translationCategory
     */
    public function setTranslationCategory($translationCategory)
    {
        $this->translationCategory = $translationCategory;
    }

    /**
     * @return \yii\db\ActiveRecord
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param \yii\db\ActiveRecord $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return ActiveForm
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param ActiveForm $form
     */
    public function setForm($form)
    {
        $this->form = $form;
    }

    /**
     * @return bool
     */
    public function getHideButtons()
    {
        return $this->hideButtons;
    }

    /**
     * @param bool $hideButtons
     */
    public function setHideButtons($hideButtons)
    {
        $this->hideButtons = $hideButtons;
    }

    /**
     * @return bool
     */
    public function isSeeStatusButtonId()
    {
        return $this->seeStatusButtonId;
    }

    /**
     * @param bool $seeStatusButtonId
     */
    public function setSeeStatusButtonId($seeStatusButtonId)
    {
        $this->seeStatusButtonId = $seeStatusButtonId;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (isset(\Yii::$app->params['hideWorkflowTransitionWidget']) && \Yii::$app->params['hideWorkflowTransitionWidget']) {
            return '';
        }
        $content = '';
        $module = ($this->module);
        if ($this->model->hasWorkflowStatus()) {
            $buttons = $this->getButtons();
            $status = $module::t($this->translationCategory, $this->getLabelStatus());
            $js = "
                $('form').on('submit', function (e) {
                    var buttonId = $('[clicked=true]').attr('id');
                    if (!buttonId) {
                        buttonId = '" . $this->model->getWorkflowStatus()->getId() . "'
                    }
                    $('#workflow-status_id').val(buttonId);
                });
                
                $('[type=submit]').on('click', function(){
                    $('[type=submit]').removeAttr('clicked');
                    $(this).attr('clicked', true);
                });
                "
                . ((isset($this->customJs)) ? $this->customJs : "");

            $this->getView()->registerJs($js, \yii\web\View::POS_READY);

            $iconToShow = $this->getIconStatus();
            if (empty($iconToShow)) {
                $iconToShow = $this->icon;
                if ($this->model->hasMethod('getValidatedStatus')) {
                    if ($this->model->status == $this->model->getValidatedStatus()) {
                        $iconToShow = $this->iconValidate;
                    } elseif ($this->model->status == $this->model->getDraftStatus()) {
                        $iconToShow = $this->iconEdit;
                    } else {
                        $iconToShow = $this->icon;
                    }
                }
            }

            $content = '<div class="' . $this->containerWidgetClass . '">'
                . '<div class="col-md-4 col-xs-12">'
                . '<div' . (isset($this->classDivIcon) ? ' class="' . $this->classDivIcon . '"' : '') . '>'
                . AmosIcons::show($iconToShow, $this->iconOptions)
                . '</div>'
                . '<div' . (isset($this->classDivMessage) ? ' class="' . $this->classDivMessage . '"' : '') . '>'
                . '<h3>' . (isset($this->initialMessage) ? $this->initialMessage : BaseAmosModule::t('amoscore', 'CURRENT STATE')) . '</h3>'
                . '<h4>' . ((isset($status) && (strlen($status) > 0)) ? $status : (isset($this->initialLabel) ? $this->initialLabel : BaseAmosModule::t('amoscore', 'Not set'))) . '</h4>'
                . '</div>'
                . '</div>';
            if (!empty($buttons)) {
                $content .= '<div class="col-md-8 col-xs-12"' . (isset($this->classDivButton) ? ' class="' . $this->classDivButton . '"' : '') . '>' . $buttons . '</div>'
                    . $this->form->field($this->model, 'status', ['options' => ['style' => 'display:none;']])->widget(Select2::classname(), [
                        'options' => ['id' => 'workflow-status_id'],
                        'data' => $this->statuses,
                    ])->label(false);
            }
            $content .= '</div>';
        } elseif ($this->viewWidgetOnNewRecord && $this->model->isNewRecord && $this->model->{$this->model->statusAttribute}) {
            /**
             * This piece of code is used for print widget when the model is new and not yet saved.
             * It print the status of the model if it is set.
             */
            $modelStatus = $this->model->{$this->model->statusAttribute};
            $status = $module::t($this->translationCategory, $this->getLabelStatus($modelStatus));
            $content = '
            <div class="' . $this->containerWidgetClass . '">
                <div class="col-md-4 col-xs-12">
                    <div' . (isset($this->classDivIcon) ? ' class="' . $this->classDivIcon . '"' : '') . '>
                        ' . AmosIcons::show($this->icon, $this->iconOptions) . '
                    </div>
                    <div' . (isset($this->classDivMessage) ? ' class="' . $this->classDivMessage . '"' : '') . '>
                        <h3>' . (isset($this->initialMessage) ? $this->initialMessage : BaseAmosModule::t('amoscore', 'CURRENT STATE')) . '</h3>
                        <h4>' . ((isset($status) && (strlen($status) > 0)) ? $status : (isset($this->initialLabel) ? $this->initialLabel : BaseAmosModule::t('amoscore', 'Not set'))) . '</h4>
                    </div>
                </div>
            </div>
            ';
        }
        return $content;
    }

    /**
     * @return string
     */
    private function getButtons()
    {
        if ($this->hideButtons) {
            return '';
        }

        $this->statuses = $this->getStatuses();

        $currentStatus = $this->getCurrentStatus();
        $buttonsArr = [];
        $buttons = "";
        $module = ($this->module);

        $User = \Yii::$app->getUser();
        $inState = $this->model->getWorkflowStatus()->getId();

        /** @var Record $nameClass */
        $nameClass = $this->model->className();
        $pk = $this->model->getPrimaryKey();
        $findOneKey = (!empty($pk) ? $pk : $this->model->id);
        $realState = $nameClass::findOne($findOneKey);

        if ($realState) {
            $this->model->status = $realState->status;
        }

        //if there are statuses with enabled comment on change status popup
        $commentStatusChange = false;

        foreach ($this->statuses as $key => $State) {
            /** @var Status $state */
            $state = $this->model->getWorkflowSource()->getStatus($key, $this->model);
            if ($state && ($key != $inState)) {
                if ($User->can($state->getId(), ['model' => $this->model])) {
                    $statusesArr[$key] = $state;
                    $metadati = $state->getMetaData();
                    $hiddenRoles = false;
                    if (isset($metadati['hiddenRoles'])) {
                        if (strpos($metadati['hiddenRoles'], ',')) {
                            $arrRoles = explode(',', $metadati['hiddenRoles']);
                            foreach ($arrRoles as $Role) {
                                if ($User->can(trim($Role))) {
                                    $hiddenRoles = TRUE;
                                }
                            }
                        } else {
                            $hiddenRoles = $User->can($metadati['hiddenRoles']);
                        }
                    }
                    if ((!isset($metadati['hidden']) || (isset($metadati['hidden']) && strtolower($metadati['hidden']) != 'true')) && !$hiddenRoles) {

                        if (!empty($metadati[$currentStatus . '_label'])) {
                            $buttonLabel = $module::t($this->translationCategory, $metadati[$currentStatus . '_label']);
                        } else if (isset($metadati['buttonLabel'])) {
                            $buttonLabel = $module::t($this->translationCategory, $metadati['buttonLabel']);
                        } else {
                            $buttonLabel = (isset($metadati['label']) ? ($module::t($this->translationCategory, $metadati['label'])) : BaseAmosModule::t('amoscore', 'Change status'));
                        }

                        $dataConfirm = (isset($metadati[$currentStatus . '_message']) ? $module::t($this->translationCategory, $metadati[$currentStatus . '_message']) : ((isset($metadati['message'])) ? $module::t($this->translationCategory, $metadati['message']) : (isset($this->dataConfirm) ? $this->dataConfirm : BaseAmosModule::t('amoscore', 'Are you sure you want to change status?'))));
                        //if enabled, print comments text area on change status data confirm popup
                        if (isset($metadati['comment'])) {
                            $commentStatusChange = true;
                            $dataConfirm .= '<div class="m-t-15">' .
                                Html::textarea('comment_' . $currentStatus, null, ['placeholder' => BaseAmosModule::t('amoscore', '#change_status_comment_placeholder'), 'maxlength' => 500, 'rows' => 3, 'class' => 'changeStatusComment full-width',
                                    'onchange' => "$('#" . Html::getInputId($this->model, 'changeStatusComment') . "').val($(this).val());"
                                ]) .
                                '</div>';
                        }

                        $closeSaveBtnWidgetConf = [
                            'model' => $this->model,
                            'layout' => $this->buttonLayout,
                            'buttonSaveLabel' => $buttonLabel,
                            'buttonNewSaveLabel' => $buttonLabel,
                            'buttonClassSave' => (isset($metadati['class']) ? $metadati['class'] : 'btn btn-navigation-primary'),
                            'buttonId' => $key,
                            'dataConfirm' => $dataConfirm
                        ];

                        // For debug.
                        if (\Yii::$app->user->can('SEE_STATUS_BUTTON_ID') && $this->seeStatusButtonId) {
                            $closeSaveBtnWidgetConf['buttonTitleSave'] = $key;
                        }

                        $buttonsArr[] = [
                            'html' =>   Html::beginTag('div', ['class' => 'workflow-info']) .
                                            Html::beginTag('span', ['class' => 'workflow-description']) .
                                                (isset($metadati[$currentStatus . '_description']) ? $module::t($this->translationCategory, $metadati[$currentStatus . '_description']) : ((isset($metadati['description'])) ? $module::t($this->translationCategory, $metadati['description']) : '')) .
                                            Html::endTag('span') .
                                            CloseSaveButtonWidget::widget($closeSaveBtnWidgetConf) .
                                        Html::endTag('div'),
                            'order' => ((isset($metadati['order']) && is_numeric($metadati['order'])) ? $metadati['order'] : 0)
                        ];
                    }
                }
            }
        } // end statuses foreach

        if (!empty($buttonsArr)) {
            $buttons = $this->getOrderedButtons($buttonsArr);
        }
        // workflow logs module is enabled
        // and comment is enabled on any status transition
        // print form hidden field to save comment value in workflow_log table
        $workflowModule = \Yii::$app->getModule('workflow');
        if (!is_null($workflowModule) && $commentStatusChange) {
            $commentField = $this->form->field($this->model, 'changeStatusComment')->hiddenInput()->label(false);
        } else {
            $commentField = '';
        }

        return $buttons . $commentField;
    }

    /**
     * @return array
     */
    private function getStatuses()
    {
        $workFlowStatus = [];   // Stati del workflow

        if ($this->model->hasWorkflowStatus()) {  // Ho già lo stato. Model già salvato una volta.
            $allStatus = $this->model->getWorkflow()->getAllStatuses();   // Tutti gli stati del workflow
            $modelStatus = $this->model->getWorkflowStatus()->getId();    // Stato del model
            /** @var Status $actualStatusObj */
            $actualStatusObj = $allStatus[$modelStatus];
            $workFlowStatus[$actualStatusObj->getId()] = $actualStatusObj->getLabel();    // Aggiungo lo stato iniziale a quelli da visualizzare.
            // Composizione di tutti gli altri stati possibili a partire dall'attuale, ovvero le transazioni possibili.
            $transitions = $this->model->getWorkflowSource()->getTransitions($modelStatus, $this->model);
            foreach ($transitions as $transition) {
                /** @var Transition $transition */
                $workFlowStatus[$transition->getEndStatus()->getId()] = $transition->getEndStatus()->getLabel();
            }
        } else {                                // Non ho lo stato. Model mai salvato. Faccio vedere solo quello iniziale.
            /** @var Workflow $contentDefaultWorkflow */
            $contentDefaultWorkflow = $this->model->getWorkflowSource()->getWorkflow($this->workflowId);
            $allStatus = $contentDefaultWorkflow->getAllStatuses();     // Tutti gli stati del workflow
            /** @var Status $initialStatusObj */
            $initialStatusObj = $allStatus[$contentDefaultWorkflow->getInitialStatusId()];
            $workFlowStatus[$initialStatusObj->getId()] = $initialStatusObj->getLabel();
        }

        return $workFlowStatus;
    }

    /**
     * @param array $buttons_array
     * @return string
     */
    private function getOrderedButtons($buttons_array)
    {
        $buttons = "";
        $ind = 0;
        $htmls = [];
        $order = [];
        foreach ($buttons_array as $key => $value) {
            $htmls[] = $value['html'];
            $order[] = $value['order'];
        }
        array_multisort($order, $htmls);
        foreach ($htmls as $html) {
            $buttons .= (($ind == 0) ? '' : ' <br><hr class="' . $this->classHr . '">') . $html;
            $ind++;
        }
        return $buttons;
    }

    /**
     * This method return the workflow status label if is set in the metadata field.
     * @param string $key
     * @return string
     */
    private function getLabelStatus($key = '')
    {
        if (!$key) {
            $key = $this->model->getWorkflowStatus()->getId();
        }
        /** @var Status $state */
        $state = $this->model->getWorkflowSource()->getStatus($key, $this->model);
        $label = '';
        if ($state) {
            $metadati = $state->getMetaData();
            if (isset($metadati['label'])) {
                $label = $metadati['label'];
            } else {
                $label = $state->label;
            }
        }
        return $label;
    }

    /**
     * This method return the workflow status icon if is set in the metadata field.
     * @param string $key
     * @return string
     */
    private function getIconStatus($key = '')
    {
        if (!$key) {
            $key = $this->model->getWorkflowStatus()->getId();
        }
        /** @var Status $state */
        $state = $this->model->getWorkflowSource()->getStatus($key, $this->model);
        $icon = '';
        if ($state) {
            $metadati = $state->getMetaData();
            if (isset($metadati['icon'])) {
                $icon = $metadati['icon'];
            }
        }
        return $icon;
    }

    /**
     * Return the id of the current state of the model without the prefix of the workflow
     * @return string
     */
    private function getCurrentStatus()
    {
        /** @var Status $state */
        $state = $key = $this->model->getWorkflowStatus()->getId();
        if (!empty(trim($key)) && strpos($key, '/') !== false) {
            $pos = strpos($key, '/');
            $state = substr($state, $pos + 1);
        }
        return $state;
    }
}
