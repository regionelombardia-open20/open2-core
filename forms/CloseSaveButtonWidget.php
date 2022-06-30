<?php

/**
 * Aria S.p.A.
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
use yii\helpers\Url;

/**
 * Class CloseSaveButtonWidget
 * Renders the close and submit buttons also according to the permissions that the user has.
 *
 * @package open20\amos\core\forms
 */
class CloseSaveButtonWidget extends Widget
{
    public $layout = "<div id=\"form-actions\" class=\"wrap-button\">{buttonSubmit}{buttonClose}</div>";
    public $buttonSaveLabel;
    public $buttonNewSaveLabel;
    public $buttonTitleSave = '';
    public $buttonClassSave = 'btn btn-primary';
    public $buttonClassClose = 'btn btn-secondary';
    public $buttonId;
    public $dataConfirm;
    public $dataTarget;
    public $dataToggle;
    private $permissionSave;
    private $urlClose;
    private $closeButtonLabel;
    private $buttonCloseVisibility = true;

    /**
     * @var \open20\amos\core\record\Record $model
     */
    private $model;

    /**
     *
     * Set of the permissionSave
     */
    public function init()
    {
        $actionName = Yii::$app->controller->action->id;
        $function = new \ReflectionClass($this->model->className());
        $modelName = $function->getShortName();
        $this->permissionSave = PermissionHelper::findPermissionModelAction($modelName, $actionName);

        parent::init();

        $this->initVariablesI18n();
    }

    public function initVariablesI18n()
    {
        if (empty($this->buttonSaveLabel)) {
            $this->setSaveLabel(BaseAmosModule::t('amoscore', '#save'));
        }
        if (empty($this->buttonNewSaveLabel)) {
            $this->buttonNewSaveLabel = BaseAmosModule::t('amoscore', '#create');
        }
    }

    public function getSaveLabel()
    {
        return $this->buttonSaveLabel;
    }

    public function setSaveLabel($label)
    {
        $this->buttonSaveLabel = $label;
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
     * @return mixed
     */
    public function getUrlClose()
    {
        return $this->urlClose;
    }

    /**
     * @param mixed $urlClose
     */
    public function setUrlClose($urlClose)
    {
        $this->urlClose = $urlClose;
    }

    /**
     * @return mixed
     */
    public function getCloseButtonLabel()
    {
        return $this->closeButtonLabel;
    }

    /**
     * @param mixed $closeButtonLabel
     */
    public function setCloseButtonLabel($closeButtonLabel)
    {
        $this->closeButtonLabel = $closeButtonLabel;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    public function run()
    {
        $content = preg_replace_callback("/{\\w+}/",
            function ($matches) {
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
            case '{buttonClose}':
                return $this->renderButtonClose();
            case '{buttonSubmit}':
                return $this->renderButtonSubmit();
            default:
                return false;
        }
    }

    public function renderButtonClose()
    {
        $options = $this->getOptionsClose();
        if (!$this->buttonCloseVisibility) {
            return '';
        }
        $urlClose = Url::previous();
        $closeButtonLabel = BaseAmosModule::t('amoscore', 'Annulla');
        if (isset($this->urlClose)) {
            $urlClose = $this->urlClose;
        }
        if (isset($this->closeButtonLabel)) {
            $closeButtonLabel = $this->closeButtonLabel;
        }
        return Html::a($closeButtonLabel, $urlClose, ['class' => $options['class']]);
    }

    private function getOptionsClose()
    {
        $options = [
            'class' => $this->buttonClassClose,
        ];

        return $options;
    }

    public function renderButtonSubmit()
    {
        if ($this->checkRenderSubmitButtonMetadata()) {
            $options = $this->getOptionsSave();
            return Html::submitButton(
                $this->model->isNewRecord ? $this->buttonNewSaveLabel : $this->buttonSaveLabel, 
		$options, 
		$this->permissionSave, 
		['model' => $this->model]
	    );
        } else {
            return '';
        }
    }

    /**
     * Check that the status of the entity passed to the widget has the metadata "submitVisible"...
     * @return bool
     */
    private function checkRenderSubmitButtonMetadata()
    {
        $metadata = 'submitVisible';
        $renderButton = true;
        if (!empty($this->model)) {
            /** @var Record $model */
            $model = $this->model;
            if (method_exists($model, 'hasWorkflowStatus')) {
                if ($model->hasWorkflowStatus()) {
                    $visible = $model->getWorkflowStatus()->getMetadata($metadata);
                    if (!empty($visible) && (strtoupper($visible) == 'NO')) {
                        $renderButton = false;
                    }
                }
            }
        }
        return $renderButton;
    }

    private function getOptionsSave()
    {
        $options = [
            'class' => $this->buttonClassSave,
        ];
        if (isset($this->buttonId)) {
            $options['id'] = $this->buttonId;
        }
        if (isset($this->dataConfirm)) {
            $options['data-confirm'] = $this->dataConfirm;
        }
        if (isset($this->dataTarget)) {
            $options['data-target'] = $this->dataTarget;
        }
        if (isset($this->dataToggle)) {
            $options['data-toggle'] = $this->dataToggle;
        }
        if (strlen($this->buttonTitleSave) > 0) {
            $options['title'] = $this->buttonTitleSave;
        }
        return $options;
    }

    /**
     * @return bool
     */
    public function isButtonCloseVisibility()
    {
        return $this->buttonCloseVisibility;
    }

    /**
     * @param bool $buttonCloseVisibility
     */
    public function setButtonCloseVisibility($buttonCloseVisibility)
    {
        $this->buttonCloseVisibility = $buttonCloseVisibility;
    }
}
