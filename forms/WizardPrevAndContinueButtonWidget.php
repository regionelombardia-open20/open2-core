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

use open20\amos\core\helpers\Html;
use open20\amos\core\helpers\PermissionHelper;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\record\Record;
use Yii;
use yii\base\Widget;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;

/**
 * Class WizardPrevAndContinueButtonWidget
 * @package open20\amos\core\forms
 */
class WizardPrevAndContinueButtonWidget extends Widget
{
    /**
     * @var string $layout
     */
    public $layout = '<div class="bk-btnFormContainer col-xs-12 nop"><div class="col-sm-6 nop">{buttonPrevious}</div><div class="col-sm-6 nop">{buttonContinue}{buttonCancel}</div></div>';
    public $continueLabel = '';
    public $continueGrahpic = '';
    public $continueOptions = [];
    public $viewPreviousBtn = true;
    public $viewContinueBtn = true;
    public $previousLabel = '';
    public $previousGrahpic = '';
    public $previousUrl = '';
    public $previousOptions = [];
    public $cancelUrl = '';
    public $finishUrl = '';
    
    /**
     * @var bool $contentAlreadyExists If true means that the record of a content is present in the db in draft status.
     */
    public $contentAlreadyExists = false;
    
    /**
     * @var Record $model
     */
    private $model;
    
    /**
     * @var bool $permissionSave
     */
    private $permissionSave;

    /**
     * @var bool $disableLinksAfterClick If true disable all links after click. It NOT disable the continue submit button.
     */
    public $disableLinksAfterClick = false;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        $actionName = Yii::$app->controller->action->id;
        $function = new \ReflectionClass($this->model->className());
        $modelName = $function->getShortName();
        $this->permissionSave = PermissionHelper::findPermissionModelAction($modelName, $actionName);
        
        parent::init();
    }
    
    /**
     * @return Record
     */
    public function getModel()
    {
        return $this->model;
    }
    
    /**
     * @param Record $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }
    
    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }
    
    /**
     * @inheritdoc
     */
    public function run()
    {
        $content = preg_replace_callback("/{\\w+}/", function ($matches) {
            $content = $this->renderSection($matches[0]);
            return $content === false ? $matches[0] : $content;
        }, $this->layout);

        $this->registerWidgetJavascripts();

        return $content;
    }
    
    /**
     * Renders a section of the specified name.
     * If the named section is not supported, false will be returned.
     * @param string $name the section name, e.g., `{summary}`, `{items}`.
     * @return string|boolean the rendering result of the section, or false if the named section is not supported.
     */
    public function renderSection($name)
    {
        switch ($name) {
            case '{buttonContinue}':
                return $this->renderButtonContinue();
            case '{buttonPrevious}':
                return $this->renderButtonPrevious();
            case '{buttonCancel}':
                return $this->renderButtonCancel();
            default:
                return false;
        }
    }
    
    /**
     * Render the continue button.
     * @return string
     */
    public function renderButtonContinue()
    {
        if (!$this->viewContinueBtn) {
            return '';
        }
        $js = <<<JS
            $('.wizard-widget-continue-btn').parents('form').on('beforeSubmit', function(event) {
                if ($(this).find(".has-error").length === 0) {
                    $('.wizard-widget-continue-btn').attr('disabled','disabled');
                }
                return true;
            });
JS;
        $this->view->registerJs($js);
        $internalOptions = ['class' => 'btn btn-primary pull-right wizard-widget-continue-btn'];
        $allOptions = ArrayHelper::merge($internalOptions, $this->continueOptions);
        if (!$this->continueLabel) {
            $this->continueLabel = BaseAmosModule::tHtml('amoscore', 'Continue');
        }
        if (!empty($this->finishUrl)) {
            $allOptions['class'] .= ' wizard-link-btn';
            return Html::a($this->continueLabel . $this->continueGrahpic, $this->finishUrl, $allOptions);
        }
//        if (!$this->continueGrahpic) {
//            $this->continueGrahpic = ' <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>';
//        }
        return Html::submitButton($this->continueLabel . $this->continueGrahpic, $allOptions, $this->permissionSave, ['model' => $this->model]);
    }
    
    /**
     * Render the previous button.
     * @return string
     */
    public function renderButtonPrevious()
    {
        if (!$this->viewPreviousBtn) {
            return '';
        }
        
        $internalOptions = ['class' => 'btn btn-action-primary pull-left wizard-link-btn'];
        $allOptions = ArrayHelper::merge($internalOptions, $this->previousOptions);
        if (!$this->previousLabel) {
            $this->previousLabel = BaseAmosModule::tHtml('amoscore', 'Go back');
        }
//        if (!$this->previousGrahpic) {
//            $this->previousGrahpic = '<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span> ';
//        }
        $urlPrevious = (strlen($this->previousUrl) ? $this->previousUrl : Url::previous());
        return Html::a($this->previousGrahpic . $this->previousLabel, $urlPrevious, $allOptions);
    }
    
    /**
     * Render the previous button.
     * @return string
     */
    public function renderButtonCancel()
    {
        if (empty($this->cancelUrl)) {
            return '';
        }
        
        $modalConfirmBtnLabel = BaseAmosModule::tHtml('amoscore', '#exit');
        $btnLinkLabel = BaseAmosModule::tHtml('amoscore', '#cancel');
        $modalDescriptionText = BaseAmosModule::tHtml('amoscore', '#close_wizard_confirmation_popup');
        if ($this->contentAlreadyExists) {
            $modalConfirmBtnLabel = BaseAmosModule::tHtml('amoscore', '#confirm');
            $btnLinkLabel = BaseAmosModule::tHtml('amoscore', '#close');
            $modalDescriptionText = BaseAmosModule::tHtml('amoscore', '#close_wizard_stop_popup');
        }
        
        Modal::begin(['id' => 'closeWizardConfirm']);
        echo Html::tag('div', $modalDescriptionText);
        echo Html::tag('div',
            Html::a(BaseAmosModule::tHtml('amoscore', '#cancel'), null, ['class' => 'btn btn-secondary', 'data-dismiss' => 'modal']) .
            Html::a($modalConfirmBtnLabel, [$this->cancelUrl], ['class' => 'btn btn-navigation-primary']),
            [
                'class' => 'pull-right m-15-0'
            ]
        );
        Modal::end();
        
        return Html::a($btnLinkLabel, $this->cancelUrl, ['data-toggle' => 'modal', 'data-target' => '#closeWizardConfirm', 'class' => 'btn btn-secondary wizard-link-btn']);
    }

    protected function registerWidgetJavascripts()
    {
        $jsContinue = <<<JS
            $('.wizard-widget-continue-btn').parents('form').on('beforeSubmit', function(event) {
                if ($(this).find(".has-error").length === 0) {
                    $('.wizard-widget-continue-btn').attr('disabled', 'disabled');
                }
                return true;
            });
JS;
        $this->view->registerJs($jsContinue, View::POS_READY);

        if ($this->disableLinksAfterClick) {
            $jsLinks = <<<JS
                $('.wizard-link-btn').on('click', function(event) {
                    $(this).attr('disabled', 'disabled');
                });
JS;
            $this->view->registerJs($jsLinks, View::POS_READY);
        }
    }
}
