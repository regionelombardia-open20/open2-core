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
use lispa\amos\core\record\Record;
use yii\bootstrap\ActiveField as YiiActiveField;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseHtml;
use yii\db\ActiveRecord;

/**
 * Class ActiveField
 * @package lispa\amos\core\forms
 */
class ActiveField extends YiiActiveField
{
    /**
     * @var string this property holds a custom input id if it was set using [[inputOptions]] or in one of the
     * `$options` parameters of the `input*` methods.
     */
    private $_inputId;

    /**
     * @var bool if "for" field label attribute should be skipped.
     */
    private $_skipLabelFor = false;

    /**
     * @var string $labelTranslationField
     */
    private $labelTranslationField;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->errorOptions = ArrayHelper::merge($this->errorOptions, [
                'tag' => 'span'
        ]);
        $this->hintOptions  = ArrayHelper::merge($this->hintOptions, [
                'tag' => 'span'
        ]);

        $hint = $this->model->getAttributeHint($this->attribute);
        if (!empty($hint)) {
            $this->parts['{hint}'] = Html::tag('span', AmosIcons::show('help'),
                    [
                    //'class' => 'text-right',
                    'data-toggle' => "tooltip",
                    'data-placement' => "top",
                    'title' => html_entity_decode($hint)
            ]);
        }
        $error = $this->model->getErrors($this->attribute);
//        if (count($error)) {
//            $this->parts['{error}'] = Html::tag('span', AmosIcons::show('alert-triangle', [], AmosIcons::AM),
//                    [
//                    //'class' => 'text-right',
//                    'data-toggle' => "tooltip",
//                    'data-placement' => "top",
//                    'title' => html_entity_decode(implode("\n", $error))
//            ]);
//        }
    }

    /**
     * @inheritdoc
     */
    public function checkboxList($items, $options = [])
    {
        if ($this->inline) {
            if (!isset($options['template'])) {
                $this->template = $this->inlineCheckboxListTemplate;
            } else {
                $this->template = $options['template'];
                unset($options['template']);
            }
            if (!isset($options['itemOptions'])) {
                $options['itemOptions'] = [
                    'labelOptions' => ['class' => 'checkbox-inline'],
                ];
            }
        } elseif (!isset($options['item'])) {
            $itemOptions     = isset($options['itemOptions']) ? $options['itemOptions'] : [];
            $options['item'] = function ($index, $label, $name, $checked, $value) use ($itemOptions) {
                $options = array_merge(['value' => $value, 'id' => $name.$value, 'name' => $name], $itemOptions);
                return '<div class="checkbox"><label class="no-asterisk" for="'.$name.$value.'">'.Html::checkbox($name,
                        $checked, $options).$label.'</label></div>';
            };
        }
        $this->checkboxListAccessible($items, $options);
        return $this;
    }

    /**
     * @param array $items
     * @param array $options
     * @return $this
     */
    public function checkboxListAccessible($items, $options = [])
    {
        $this->addAriaAttributes($options);
        $this->adjustLabelFor($options);
        $this->_skipLabelFor    = true;
        $this->parts['{input}'] = Html::activeCheckboxList($this->model, $this->attribute, $items, $options);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function radioList($items, $options = [])
    {
        if ($this->inline) {
            if (!isset($options['template'])) {
                $this->template = $this->inlineRadioListTemplate;
            } else {
                $this->template = $options['template'];
                unset($options['template']);
            }
            if (!isset($options['itemOptions'])) {
                $options['itemOptions'] = [
                    'labelOptions' => ['class' => 'radio-inline'],
                ];
            }
        } elseif (!isset($options['item'])) {
            $itemOptions     = isset($options['itemOptions']) ? $options['itemOptions'] : [];
            $options['item'] = function ($index, $label, $name, $checked, $value) use ($itemOptions) {
                $options = array_merge(['value' => $value, 'id' => $name.$value, 'name' => $name], $itemOptions);
                return '<div class="radio"><label for="'.$name.$value.'">'.Html::radio($name, $checked, $options).$label.'</label></div>';
            };
        }
        parent::radioList($items, $options);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function label($label = null, $options = [])
    {
        $this->setTemplateByTranslation();
        if (is_bool($label)) {
            $this->enableLabel = $label;
            if ($label === false && $this->form->layout === 'horizontal') {
                Html::addCssClass($this->wrapperOptions, $this->horizontalCssClasses['offset']);
                //Da verificare prima sulle view di kartik
//                $this->enableLabel = true;
//                $label = ucfirst($this->attribute);
//                $this->renderLabelParts($label, $options);
//                $options['class'] = 'sr-only';
//                parent::label($label, $options);
            } else {
                $this->enableLabel = true;
                $label             = ucfirst($this->attribute);
                $this->renderLabelParts($label, $options);
                $options['class']  = 'sr-only';
                parent::label($label, $options);
            }
        } else {
            if ($label == 'AmosHidden') {
                $this->enableLabel = false;
                $label             = false;
                parent::label($label, $options);
            } else {
                $this->enableLabel = true;
                $this->renderLabelParts($label, $options);
                parent::label($label, $options);
            }
        }
        return $this;
    }

    /**
     * An extended textarea from base function that includes a counter of inserted characters in the generated
     * textarea. The max number of characters is gotten dinamically from the maxchars attr of the generated textarea.
     * If maxlength is not defined from a rule in the model, it will be set from the column size from the schema.
     * If maxlength defined from the options is greater than column size of the schema, the maxlength
     * will be set with the schema column size.
     * @param array $options
     * @return $this
     */
    public function limitedCharsTextarea($options = [])
    {
        $textareaId = Html::getInputId($this->model, $this->attribute);

        $size = 255;
        if (empty($options['maxlength'])) {
            if ($this->model instanceof ActiveRecord) {
                $size = $this->model->getTableSchema()->columns[$this->attribute]->size;
            }
        } else {
            $size = $options['maxlength'];
        }

        $script = <<<JS
            var maxlength = 0;
            var schemaSize = "{$size}";
            
            if(schemaSize === "") {
                schemaSize = 0
            }
            
            if($("#{$textareaId}").attr("maxlength")===undefined){
                maxlength = schemaSize;
                $("#{$textareaId}").attr("maxlength", maxlength);
            } else {
                if($("#{$textareaId}").attr("maxlength") < schemaSize) {
                    maxlength = $("#{$textareaId}").attr("maxlength");
                } else {
                    maxlength = schemaSize;
                    $("#{$textareaId}").attr("maxlength", maxlength);
                }
                    
            }
            
            function getValue() {
                return $("#{$textareaId}").val();
            }
            
            function getHttpValue() {
                return getValue().replace(/\\n/g, "\\r\\n").trim();
            }
            
            function geNumChars() {
                return getHttpValue().length;
            }
            
            function setValueByHttp(valueHttp) {
                $("#{$textareaId}").val(valueHttp.replace(/\\r\\n/g, "\\n").trim());
            }
            
            var countChars = $("#{$textareaId}").parent().parent().children()[0];
            var numChars = geNumChars();
            
            $(countChars).append('<div class="count-char-{$textareaId} pull-right"><span class="chars">0</span>/'+maxlength+'</div>');
            
            
            $(".count-char-{$textareaId} .chars").text(numChars);

            $("#{$textareaId}").bind('input keydown', function(e) {
                numChars = geNumChars();
                
                if(numChars >= maxlength && maxlength != 0) {
                    var content = getHttpValue();
                    var truncatedContent = content.substring(0,maxlength);
                    
                    setValueByHttp(truncatedContent);
                }
                
                //Reset count
                numChars = geNumChars();
                
                $(".count-char-{$textareaId} .chars").text(numChars);
            });
JS;

        $this->form->view->registerJs($script);

        // Inject of a basic css that turns red the number of chars inserted in the textarea
        $this->form->view->registerCss(".count-char-".$textareaId." .chars { color:red; }");

        return $this->textarea($options);
    }

    /**
     * @inheritdoc
     */
    public function begin()
    {
        $currentScenario = $this->model->getScenario();
        if ($currentScenario == Record::SCENARIO_FAKE_REQUIRED) {
            $this->model->setScenario(Record::SCENARIO_DEFAULT);
            $attribute = Html::getAttributeName($this->attribute);
            if ($this->model->isAttributeRequired($attribute)) {
                Html::addCssClass($this->options, 'fake_asterisk_required');
            }
            $this->model->setScenario($currentScenario);
        }
        return parent::begin();
    }

    public function setTemplateByTranslation()
    {
        try {
            $module = \Yii::$app->getModule('translation');
            if (empty($this->labelTranslationField) && !empty($module)) {
                if (!empty($module->enableLabelTranslationField) && $module->enableLabelTranslationField === true && $module->byPassPermissionInlineTranslation
                    === true) {
                    $configuration = $module->translationBootstrap;
                    if (!empty($configuration['configuration']['translationContents'])) {
                        $translationContents = $configuration['configuration']['translationContents'];
                        if (!empty($translationContents['classBehavior']) && !empty($translationContents['models'])) {
                            foreach ($translationContents['models'] as $model) {
                                if (!empty($model['namespace']) && !empty($model['attributes'])) {
                                    $classSender = get_class($this->model);
                                    if ($classSender == $model['namespace'] && !empty($model['attributes']) && in_array($this->attribute,
                                            $model['attributes'])) {
                                        if (!empty($module->labelTranslationField)) {
                                            eval("\$translationLabelAltField = {$module->translationLabelAltField}");
                                            eval("\$translationLabelField = {$module->translationLabelField}");
                                            $templateTranslationField    = $module->templateTranslationField;
                                            $templateTranslationAltField = $module->templateTranslationAltField;
                                            $this->labelTranslationField = str_replace($templateTranslationAltField,
                                                $translationLabelAltField, $module->labelTranslationField);
                                            $this->labelTranslationField = str_replace($templateTranslationField,
                                                $translationLabelField, $this->labelTranslationField);                                      
                                        } else {
                                            $this->labelTranslationField = ' (<span class="label_translation am am-translate" title="'.\Yii::t("amostranslation",
                                                    "Testo traducibile direttamente scrivendo in questo campo, tradurrai nella lingua selezionata, la visualizzazione attuale è in").' '.strtoupper(substr(\Yii::$app->language,
                                                        0, 2)).'"> - '.strtoupper(substr(\Yii::$app->language, 0, 2)).'</span>)';
                                        }
                                        if (!empty($this->labelTranslationField)) {
                                            $posLabel = strpos($this->template, '{label}');
                                            if ($posLabel !== false && strpos($this->template,
                                                    $this->labelTranslationField) === false) {
                                                $pos            = $posLabel + 7;
                                                $this->template = substr($this->template, 0, $pos).$this->labelTranslationField.substr($this->template,
                                                        $pos);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {

        }
    }
}