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
use open20\amos\core\interfaces\WorkflowModelInterface;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\utilities\ModalUtility;
use Yii;
use yii\base\Widget;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;

/**
 * Class ContextMenuWidget
 * @package open20\amos\core\forms
 */
class ContextMenuWidget extends Widget
{
    /**
     * @var object $model
     */
    private $model;

    /**
     * @var string $layout Widget view
     */
    public $layout = "@vendor/open20/amos-core/forms/views/widgets/context_menu_widget.php";

    /**
     * @var array $optionsModify Modify options
     */
    private $optionsModify;

    /**
     * @var array $optionsDelete Delete options
     */
    private $optionsDelete;

    /**
     * @var string $actionModify Route to modify controller action
     */
    private $actionModify;

    /**
     * @var string $actionDelete Route to delete controller action
     */
    private $actionDelete;

    /**
     * @var bool $disableModify
     */
    private $disableModify = false;

    /**
     * @var bool $disableDelete
     */
    private $disableDelete = false;

    /**
     * @var string $labelDeleteConfirm Label on delete confirm
     */
    private $labelDeleteConfirm;

    /**
     * @var boolean $atLeastOnePermission If true there's at least one action to render
     */
    private $atLeastOnePermission;

    /**
     * @var string $mainDivClasses Standard widget classes. If is set in widget options, this variable is overwritten
     */
    private $mainDivClasses = '';

    /**
     * @var string
     */
    private $modelValidatePermission = '';

    /**
     * @var bool
     */
    private $checkModifyPermission = true;

    /**
     * @var bool
     */
    private $checkDeletePermission = true;

    /**
     * @var bool
     */
    private $labelModify = '';

    /**
     * @var bool
     */
    private $labelDelete = '';

    /**
     * @var string
     */
    private $confirmModify = '';

    /**
     * @var array
     */
    public $additionalButtons = [];

    /**
     * Stringa che rappresenta l'etichetta che viene visualizzata quando il mouse è sopra il widget
     */
    public $title;

    public function __construct() {

        $this->title = BaseAmosModule::t('amoscore', 'Menu contestuale');

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->initLabels();
        $buttons = $this->composeContextMenuButtons();
        return $this->renderFile($this->getLayout(), [
            'title' => $this->title,
            'atLeastOnePermission' => $this->atLeastOnePermission,
            'buttons' => $buttons,
            'mainDivClasses' => $this->getMainDivClasses()
        ]);
    }

    /**
     * This method create the buttons array. It contains the strings of html "a" tag ready to print in view.
     * @return array
     */
    private function composeContextMenuButtons()
    {
        $modifyTitle = $this->labelModify;
        $deleteTitle =  $this->labelDelete;
        $labelDeleteConfirm = ($this->getLabelDeleteConfirm() ? $this->getLabelDeleteConfirm() : BaseAmosModule::t('amoscore', 'Sei sicuro di voler eliminare questo elemento?'));

        $optionsModify = [
            'model' => $this->model,
            'title' => $modifyTitle,
        ];
        if(!empty($this->confirmModify)){
            $optionsModify ['data'] = [
                'confirm' => $this->confirmModify,
                'method' => 'post',
                'pjax' => 0
            ];
        }

        $optionsDelete = [
            'model' => $this->model,
            'title' => $deleteTitle,
            'data' => [
                'confirm' => $labelDeleteConfirm,
                'method' => 'post',
                'pjax' => 0
            ],
        ];

        if (isset($this->optionsModify) && is_array($this->optionsModify)) {
            $optionsModify = ArrayHelper::merge($optionsModify, $this->optionsModify);
        }
        $optionsModify = ArrayHelper::merge($optionsModify, ['model' => $this->model]);

        if (isset($this->optionsDelete) && is_array($this->optionsDelete)) {
            $optionsDelete = ArrayHelper::merge($optionsDelete, $this->optionsDelete);
        }
        $optionsDelete = ArrayHelper::merge($optionsDelete, ['model' => $this->model]);



        $buttons = [];
        if (!$this->isDisableModify()) {
            if(!empty($this->modelValidatePermission) && $this->model instanceof WorkflowModelInterface){
                $optionsModify = ModalUtility::getBackToEditPopup($this->model, $this->modelValidatePermission, $this->getActionModify(), $optionsModify);
            }

            $buttons[] = Html::a($modifyTitle, $this->getActionModify(), $optionsModify, $this->checkModifyPermission);
        }
        if (!$this->isDisableDelete()) {
            $buttons[] = Html::a($deleteTitle, $this->getActionDelete(), $optionsDelete, $this->checkDeletePermission);
        }

        if(!empty($this->additionalButtons)){
            foreach ($this->additionalButtons as $htmlAdditionalButton){
                  $buttons []= $htmlAdditionalButton;
            }
        }

        $this->atLeastOnePermission = false;
        foreach ($buttons as $button) {
            if (strlen($button) > 0) {
                $this->atLeastOnePermission = true;
            }
        }
        return $buttons;
    }


    /**
     * Init modifylabel and deleteLabel
     */
    protected function initLabels(){
        if(empty($this->labelModify)) {
            $this->labelModify =  BaseAmosModule::t('amoscore', 'Modifica');
        }
        if(empty($this->labelDelete)) {
            $this->labelDelete= BaseAmosModule::t('amoscore', 'Cancella');
        }
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param string $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function getLabelDeleteConfirm()
    {
        return $this->labelDeleteConfirm;
    }

    /**
     * @param string $labelDeleteConfirm
     */
    public function setLabelDeleteConfirm($labelDeleteConfirm)
    {
        $this->labelDeleteConfirm = $labelDeleteConfirm;
    }

    /**
     * @return string
     */
    public function getActionModify()
    {
        return $this->actionModify;
    }

    /**
     * @param string $actionModify
     */
    public function setActionModify($actionModify)
    {
        $this->actionModify = $actionModify;
    }

    /**
     * @return string
     */
    public function getActionDelete()
    {
        return $this->actionDelete;
    }

    /**
     * @param string $actionDelete
     */
    public function setActionDelete($actionDelete)
    {
        $this->actionDelete = $actionDelete;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @return string
     */
    public function getMainDivClasses()
    {
        return $this->mainDivClasses;
    }

    /**
     * @param string $mainDivClasses
     */
    public function setMainDivClasses($mainDivClasses)
    {
        $this->mainDivClasses = $mainDivClasses;
    }

    /**
     * @return bool
     */
    public function isDisableModify()
    {
        return $this->disableModify;
    }

    /**
     * @param bool $disableModify
     */
    public function setDisableModify($disableModify)
    {
        $this->disableModify = $disableModify;
    }

    /**
     * @return array
     */
    public function getOptionsModify()
    {
        return $this->optionsModify;
    }

    /**
     * @param array $optionsModify
     */
    public function setOptionsModify($optionsModify)
    {
        $this->optionsModify = $optionsModify;
    }

    /**
     * @return array
     */
    public function getOptionsDelete()
    {
        return $this->optionsDelete;
    }

    /**
     * @param array $optionsDelete
     */
    public function setOptionsDelete($optionsDelete)
    {
        $this->optionsDelete = $optionsDelete;
    }

    /**
     * @return bool
     */
    public function isDisableDelete()
    {
        return $this->disableDelete;
    }

    /**
     * @param bool $disableDelete
     */
    public function setDisableDelete($disableDelete)
    {
        $this->disableDelete = $disableDelete;
    }

    /**
     * @return string
     */
    public function getModelValidatePermission()
    {
        return $this->modelValidatePermission;
    }

    /**
     * @param string $modelValidatePermission
     */
    public function setModelValidatePermission($modelValidatePermission)
    {
        $this->modelValidatePermission = $modelValidatePermission;
    }


    /**
     * @return string
     */
    public function getCheckModifyPermission()
    {
        return $this->checkModifyPermission;
    }

    /**
     * @param string $modelValidatePermission
     */
    public function setCheckModifyPermission($checkModifyPermission)
    {
        $this->checkModifyPermission = $checkModifyPermission;
    }


    /**
     * @return bool
     */
    public function getCheckDeletePermission()
    {
        return $this->checkDeletePermission;
    }

    /**
     * @param $checkDeletePermission
     */
    public function setCheckDeletePermission($checkDeletePermission)
    {
        $this->checkDeletePermission = $checkDeletePermission;
    }

    /**
    * @return string
    */
    public function getLabelModify()
    {
        return $this->labelModify;
    }

    /**
     * @param $labelModify
     */
    public function setLabelModify($labelModify)
    {
        $this->labelModify = $labelModify;
    }

    /**
     * @return string
     */
    public function getLabelDelete()
    {
        return $this->labelDelete;
    }

    /**
     * @param $labelDelete
     */
    public function setLabelDelete($labelDelete)
    {
        $this->labelDelete = $labelDelete;
    }


    /**
     * @return string
     */
    public function getConfirmModify()
    {
        return $this->confirmModify;
    }

    /**
     * @param $labelDelete
     */
    public function setConfirmModify($confirmModify)
    {
        $this->confirmModify = $confirmModify;
    }


}
