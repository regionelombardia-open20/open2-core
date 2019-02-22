<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views\grid
 * @category   CategoryName
 */

namespace lispa\amos\core\views\grid;

//use Closure;
use lispa\amos\core\icons\AmosIcons;
use kartik\base\Config;
//use kartik\base\InputWidget;
//use kartik\popover\PopoverX;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
//use yii\widgets\ActiveForm;
use kartik\editable\Editable as KartikEditable;

class Editable extends KartikEditable{
    /**
     * Initializes the widget options. This method sets the default values for various widget options.
     *
     * @throws InvalidConfigException
     */
    protected function initOptions()
    {
        Html::addCssClass($this->inputContainerOptions, self::CSS_PARENT);
        if ($this->asPopover !== true) {
            $this->initInlineOptions();
        }
        if ($this->hasModel()) {
            $options = ArrayHelper::getValue($this->inputFieldConfig, 'options', []);
            Html::addCssClass($options, self::CSS_PARENT);
            $this->inputFieldConfig['options'] = $options;
        }
        if (!Config::isHtmlInput($this->inputType)) {
            if ($this->widgetClass === 'kartik\datecontrol\DateControl') {
                $options = ArrayHelper::getValue($this->options, 'options.options', []);
                Html::addCssClass($options, 'kv-editable-input');
                $this->options['options']['options'] = $options;
            } elseif ($this->inputType !== self::INPUT_WIDGET) {
                $options = ArrayHelper::getValue($this->options, 'options', []);
                Html::addCssClass($options, 'kv-editable-input');
                $this->options['options'] = $options;
            }
        } else {
            $css = empty($this->options['class']) ? ' form-control' : '';
            Html::addCssClass($this->options, 'kv-editable-input' . $css);
        }
        $this->_inputOptions = $this->options;
        $this->containerOptions['id'] = $this->options['id'] . '-cont';
        $value = $this->hasModel() ? Html::getAttributeValue($this->model, $this->attribute) : $this->value;
        if ($value === null && !empty($this->valueIfNull)) {
            $value = $this->valueIfNull;
        }
        if (!isset($this->displayValue)) {
            $this->displayValue = $value;
        }
        if ($this->valueIfNull === null || $this->valueIfNull === '') {
            $this->valueIfNull = '<em>' . Yii::t('kveditable', '(not set)') . '</em>';
        }
        if ($this->displayValue === null || $this->displayValue === '') {
            $this->displayValue = $this->valueIfNull;
        }
        $hasDisplayConfig = is_array($this->displayValueConfig) && !empty($this->displayValueConfig);
        if ($hasDisplayConfig && (is_array($this->value) || is_object($this->value))) {
            throw new InvalidConfigException(
                "Your editable value cannot be an array or object for parsing with 'displayValueConfig'. The array keys in 'displayValueConfig' must be a simple string or number. For advanced display value calculations, you must use your controller AJAX action to return 'output' as a JSON encoded response which will be used as a display value."
            );
        }
        if ($hasDisplayConfig && isset($this->displayValueConfig[$value])) {
            $this->displayValue = $this->displayValueConfig[$value];
        }
        Html::addCssClass($this->containerOptions, 'kv-editable');
        Html::addCssClass($this->contentOptions, 'kv-editable-content');
        Html::addCssClass($this->formOptions['options'], 'kv-editable-form');
        $class = 'kv-editable-value';
        if ($this->format == self::FORMAT_BUTTON) {
            if (!$this->asPopover) {
                if ($this->inlineSettings['templateBefore'] === self::INLINE_BEFORE_1) {
                    Html::addCssClass($this->containerOptions, 'kv-editable-inline-1');
                } elseif ($this->inlineSettings['templateBefore'] === self::INLINE_BEFORE_2) {
                    Html::addCssClass($this->containerOptions, 'kv-editable-inline-2');
                }
            }
            Html::addCssClass($this->editableButtonOptions, 'kv-editable-button');
        } elseif (empty($this->editableValueOptions['class'])) {
            $class = ['kv-editable-value', 'kv-editable-link'];
        }
        Html::addCssClass($this->editableValueOptions, $class);
        $this->_popoverOptions['type'] = $this->type;
        $this->_popoverOptions['placement'] = $this->placement;
        $this->_popoverOptions['size'] = $this->size;
        if (!isset($this->preHeader)) {
            $this->preHeader = AmosIcons::show('edit') . Yii::t('kveditable', 'Edit') . ' ';
        }
        if ($this->header == null) {
            $attribute = $this->attribute;
            if (strpos($attribute, ']') > 0) {
                $tags = explode(']', $attribute);
                $attribute = array_pop($tags);
            }
            $this->_popoverOptions['header'] = $this->preHeader .
                ($this->hasModel() ? $this->model->getAttributeLabel($attribute) : '');
        } else {
            $this->_popoverOptions['header'] = $this->preHeader . $this->header;
        }
        $this->_popoverOptions['footer'] = $this->renderFooter();
        $this->_popoverOptions['options']['class'] = 'kv-editable-popover skip-export';
        if ($this->format == self::FORMAT_BUTTON) {
            if (empty($this->editableButtonOptions['label'])) {
                $this->editableButtonOptions['label'] = AmosIcons::show('edit');
            }
            Html::addCssClass($this->editableButtonOptions, 'kv-editable-toggle');
            $this->_popoverOptions['toggleButton'] = $this->editableButtonOptions;
        } else {
            $this->_popoverOptions['toggleButton'] = $this->editableValueOptions;
            $this->_popoverOptions['toggleButton']['label'] = $this->displayValue;
        }
        if (!empty($this->footer)) {
            Html::addCssClass($this->_popoverOptions['options'], 'has-footer');
        }
    }

    /**
     * Renders all native HTML inputs (except [[INPUT_HTML5]])
     *
     * @return string
     */
    protected function renderInput()
    {
        $list = Config::isDropdownInput($this->inputType);
        $input = $this->inputType;
        if ($this->hasModel()) {
            if (isset($this->_form)) {
                return $list ?
                    $this->_form
                        ->field($this->model, $this->attribute, $this->inputFieldConfig)
                        ->$input(
                            $this->data, $this->_inputOptions
                        )
                        ->label($this->attribute,['class' => 'sr-only']) :
                    $this->_form
                        ->field($this->model, $this->attribute, $this->inputFieldConfig)
                        ->$input(
                            $this->_inputOptions
                        )
                        ->label($this->attribute,['class' => 'sr-only']);
            }
            $input = 'active' . ucfirst($this->inputType);
        }
        $checked = false;
        if ($input == 'radio' || $input == 'checkbox') {
            $this->options['value'] = $this->value;
            $checked = ArrayHelper::remove($this->_inputOptions, 'checked', false);
        }

        if ($list) {
            $field = Html::$input($this->name, $this->value, $this->data, $this->_inputOptions);
        } else {
            $field = ($input == 'checkbox' || $input == 'radio') ?
                Html::$input($this->name, $checked, $this->_inputOptions) :
                Html::$input($this->name, $this->value, $this->_inputOptions);
        }
        return Html::tag('div', $field, $this->inputContainerOptions);
    }
}