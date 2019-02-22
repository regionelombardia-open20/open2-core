<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\forms
 * @category   CategoryName
 */

namespace lispa\amos\core\forms;

use lispa\amos\core\helpers\Html;
use lispa\amos\core\helpers\PermissionHelper;
use lispa\amos\core\module\BaseAmosModule;
use lispa\amos\core\record\Record;
use Yii;
use yii\base\Widget;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Class WizardPrevAndContinueButtonWidget
 * @package lispa\amos\core\forms
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
        $internalOptions = ['class' => 'btn btn-primary pull-right'];
        $allOptions = ArrayHelper::merge($internalOptions, $this->continueOptions);
        if (!$this->continueLabel) {
            $this->continueLabel = BaseAmosModule::tHtml('amoscore', 'Continue');
        }
        if (!empty($this->finishUrl)) {
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
        
        $internalOptions = ['class' => 'btn btn-action-primary pull-left'];
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
        
        return Html::a($btnLinkLabel, $this->cancelUrl, ['data-toggle' => 'modal', 'data-target' => '#closeWizardConfirm', 'class' => 'btn btn-secondary']);
        
    }
}
