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
use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\utilities\FormUtility;
use yii\bootstrap\ActiveForm as YiiActiveForm;
use yii\helpers\Url;

class ActiveForm extends YiiActiveForm
{
    /**
     * Se settata a FALSE imposta le variabili fieldConfig e fieldClass a quelle di default di Amos,
     * se settata a TRUE usa quelle di default di Yii o una personalizzata se iniettata
     * @var boolean
     */
    public $customize = FALSE;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->options['role'] = false;
        $this->setFieldConfigDefault();
        
        echo FormUtility::tabErrorTriangle();
        
        parent::init();
    }
    
    /**
     * @inheritdoc
     */
    protected function getClientOptions()
    {
        $options = [
            'encodeErrorSummary' => $this->encodeErrorSummary,
            'errorSummary' => '.' . implode('.', preg_split('/\s+/', $this->errorSummaryCssClass, -1, PREG_SPLIT_NO_EMPTY)),
            'validateOnSubmit' => $this->validateOnSubmit,
            'errorCssClass' => $this->errorCssClass,
            'successCssClass' => $this->successCssClass,
            'validatingCssClass' => $this->validatingCssClass,
            'ajaxParam' => $this->ajaxParam,
            'ajaxDataType' => $this->ajaxDataType,
            'scrollToError' => $this->scrollToError,
        ];
        
        if ($this->validationUrl !== null) {
            $options['validationUrl'] = Url::to($this->validationUrl);
        }
        
        // only get the options that are different from the default ones (set in yii.activeForm.js)
        return array_diff_assoc($options, [
            'encodeErrorSummary' => true,
            'errorSummary' => '.error-summary',
            'validateOnSubmit' => true,
            'errorCssClass' => 'has-error',
            'successCssClass' => 'has-success',
            'validatingCssClass' => 'validating',
            'ajaxParam' => 'ajax',
            'ajaxDataType' => 'json',
            'scrollToError' => true,
        ]);
    }
    
    /**
     * Setta nel caso della variabile $customize settata a false la configurazione di deafault di Amos per i field dei form
     */
    protected function setFieldConfigDefault()
    {
        if ($this->customize === FALSE) {
            $this->fieldConfig = [
                'template' => "<div class=\"row\">
                    <div class=\"col-xs-12\">{label}</div>
                    \n<div class=\"col-xs-12\">{input}</div>
                    <div class=\"col-xs-12\"> <span class=\"tooltip-field\"> {hint} </span> <span class=\"tooltip-error-field\"> {error} </span> </div>
            </div>",
            ];
            $this->fieldClass = ActiveField::className();
        }
    }
}
