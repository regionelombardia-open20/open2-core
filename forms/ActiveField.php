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
use open20\amos\core\helpers\StringHelper;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\record\Record;
use yii\bootstrap\ActiveField as YiiActiveField;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseHtml;
use yii\db\ActiveRecord;
use open20\amos\core\module\BaseAmosModule;

/**
 * Class ActiveField
 * @package open20\amos\core\forms
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
    public $errorOptions   = [
        'class' => 'help-block help-block-error',
        'tag' => 'span',
    ];

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

        $this->hintOptions = ArrayHelper::merge($this->hintOptions, [
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
//        $error = $this->model->getErrors($this->attribute);
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
        if (!empty($options['labelOptions'])) {
            $this->labelOptions = $options['labelOptions'];
        }
        if (empty($this->labelOptions) || empty($this->labelOptions['for'])) {
            if (empty($this->labelOptions)) {
                $this->labelOptions = [];
            }
            $this->labelOptions = ArrayHelper::merge($this->labelOptions, ['for' => null]);
        }
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
                    $checked, $options).$label.'<span class="amos-check"></span></label></div>';
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
                $this->template      = '{label} <fieldset> {beginWrapper} {input} {error} {endWrapper} {hint} </fieldset>';
                $options['template'] = $this->template;
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
                return '<div class="radio"><label for="'.$name.$value.'">'.Html::radio($name, $checked, $options).$label.'<span class="amos-radio"></span></label></div>';
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
                if ($label === false) {
                    $this->enableLabel = false;
                    $label             = false;
                    parent::label($label, $options);
                } else {
                    $this->enableLabel = true;
                    $label             = ucfirst($this->attribute);
                    $this->renderLabelParts($label, $options);
                    $options['class']  = 'sr-only';
                }
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

        $showCount = true;
        if (isset($options['showCount'])) {
            if (!$options['showCount']) {
                $showCount = false;
            }
        }

        $defaultColorCount = 'red';
        $colorCount        = $defaultColorCount;
        if (!empty($options['colorCount'])) {
            $colorCount = $options['colorCount'];
        }

        $script = <<<JS
                
	var init_{$this->attribute} = function() {
            var maxlength = 0;
            var schemaSize = "{$size}";
            var showCount = "{$showCount}";
            
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
            
            if(showCount == true) {
                $(countChars).append('<div class="count-char-{$textareaId} pull-right"><span class="chars">0</span>/'+maxlength+'</div>');
            }
            
            
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
	};
		
	init_{$this->attribute}();
JS;

        $this->form->view->registerJs($script);

        // Inject of a basic css that turns red the number of chars inserted in the textarea
        $this->form->view->registerCss(".count-char-".$textareaId." .chars { color:".$colorCount."; }");

        return $this->textarea($options);
    }

    /**
     * Default Yii2 Text Input with char counter.
     * @param $options
     * @return ActiveField
     * @throws \yii\base\InvalidConfigException
     */
    public function limitedCharsTextInput($options = [])
    {
        $textInputId = Html::getInputId($this->model, $this->attribute);

        // Max length
        $size = 255;
        if (empty($options['maxlength'])) {
            if ($this->model instanceof ActiveRecord) {
                $size = $this->model->getTableSchema()->columns[$this->attribute]->size;
            }
        } else {
            $size = $options['maxlength'];
        }

        // Show char counter or not
        $showCount = true;
        if (isset($options['showCount'])) {
            if (!$options['showCount']) {
                $showCount = false;
            }
        }

        // Char counter color
        $defaultColorCount = 'black';
        $colorCount        = $defaultColorCount;
        if (!empty($options['colorCount'])) {
            $colorCount = $options['colorCount'];
        }

        $jsTextInput = <<<JS
                
            var init_{$this->attribute} = function() {
                var maxlength = 0;
                var schemaSize = "{$size}";
                var showCount = "{$showCount}";
                    
                if(schemaSize === "") {
                    schemaSize = 0
                }
        
                if($("#{$textInputId}").attr("maxlength")===undefined){
                    maxlength = schemaSize;
                    $("#{$textInputId}").attr("maxlength", maxlength);
                } else {
                    if($("#{$textInputId}").attr("maxlength") < schemaSize) {
                        maxlength = $("#{$textInputId}").attr("maxlength");
                    } else {
                        maxlength = schemaSize;
                        $("#{$textInputId}").attr("maxlength", maxlength);
                    }
                }
                    
                function getValue() {
                    return $("#{$textInputId}").val();
                }
                    
                function getHttpValue() {
                    return getValue().replace(/\\n/g, "\\r\\n").trim();
                }
                    
                function geNumChars() {
                    return getHttpValue().length;
                }
                    
                function setValueByHttp(valueHttp) {
                    $("#{$textInputId}").val(valueHttp.replace(/\\r\\n/g, "\\n").trim());
                }
                    
                var countChars = $("#{$textInputId}").parent().parent().children()[0];
                var numChars = geNumChars();
                    
                if(showCount == true) {
                    $(countChars).append('<div class="count-char-{$textInputId} pull-right"><span class="chars">0</span>/'+maxlength+'</div>');
                }
                    
                    
                $(".count-char-{$textInputId} .chars").text(numChars);
        
                $("#{$textInputId}").bind('input keydown', function(e) {
                    numChars = geNumChars();
                        
                    if(numChars >= maxlength && maxlength != 0) {
                        var content = getHttpValue();
                        var truncatedContent = content.substring(0,maxlength);
                        
                        setValueByHttp(truncatedContent);
                    }
                        
                    //Reset count
                    numChars = geNumChars();
                        
                    $(".count-char-{$textInputId} .chars").text(numChars);
                });
            };
                
            init_{$this->attribute}();
JS;

        $this->form->view->registerJs($jsTextInput);

        // Set char counter color
        $this->form->view->registerCss(".count-char-".$textInputId." .chars { color:".$colorCount."; }");

        return $this->textInput($options);
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
            if ($this->checkCanShowTranslationInLine()) {
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
                                                $this->labelTranslationField = ' (<span class="label_translation am am-translate" title="'.BaseAmosModule::t("amostranslation",
                                                        "Testo traducibile direttamente scrivendo in questo campo, tradurrai nella lingua selezionata, la visualizzazione attuale è in").' '.strtoupper(substr(\Yii::$app->language,
                                                            0, 2)).'"> - '.strtoupper(substr(\Yii::$app->language, 0, 2)).'</span>)';
                                            }
                                            if (!empty($this->labelTranslationField)) {
                                                $appLanguage = \open20\amos\core\i18n\MessageSource::getMappedLanguage(\Yii::$app->language);

                                                if (!empty($module->defaultLanguage) && ($appLanguage == $module->defaultLanguage)) {
                                                    $textSource = '';
                                                } else {
                                                    $textSource = $this->translationSource($this->model->id, $module,
                                                        $model['namespace']);
                                                }
                                                $posLabel = strpos($this->template, '{label}');
                                                if ($posLabel !== false && strpos($this->template,
                                                        $this->labelTranslationField) === false) {
                                                    $pos            = $posLabel + 7;
                                                    $this->template = $textSource.substr($this->template, 0, $pos).$this->labelTranslationField.substr($this->template,
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
            }
        } catch (\Exception $e) {

        }
    }

    /**
     * @return bool
     */
    public function checkCanShowTranslationInLine()
    {
        $okMultilanguage = true;

        if (method_exists($this->model, 'canShowTranslationInLine')) {
            return $this->model->canShowTranslationInLine();
        }

        if (!empty(\Yii::$app->params['showTranslationInlinePersonalized'])) {
            $class           = \Yii::$app->params['showTranslationInlinePersonalized']['class'];
            $method          = \Yii::$app->params['showTranslationInlinePersonalized']['method'];
            $okMultilanguage = $class::$method();
            return $okMultilanguage;
        }

        return true;
    }

    public function isMultilanguageEnabled()
    {
        $moduleCwh    = \Yii::$app->getModule('cwh');
        $moduleEvents = \Yii::$app->getModule('events');
        if (!empty($moduleCwh) && !empty($moduleEvents)) {
            $scope = $moduleCwh->getCwhScope();
            if (!empty($scope) && isset($scope['community'])) {
                $communityId = $scope['community'];
                $event       = \open20\amos\events\models\Event::find()->andWhere(['community_id'])->one();
                if ($event->multilanguage) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     *
     * @param type $id
     * @param type $module
     * @param type $namespace
     * @param type $default_language
     * @return string
     */
    public function translationSource($id, $module, $namespace, $default_language = 'it-IT')
    {
        $str = '';
        if (!$this->model->isNewRecord) {
            $lang = \open20\amos\core\i18n\MessageSource::getMappedLanguage(\Yii::$app->language);
            if ($lang != $default_language) {
                $classNameTrans  = $module->modelNs.'\\'.StringHelper::basename($namespace)."Translation";
                $language_source = \Yii::$app->request->getQueryParam(StringHelper::basename($classNameTrans))['language_source'];
                list($language_source_res, $source) = \open20\amos\translation\models\TranslationConf::getSource($language_source,
                        $id, $lang, $namespace);

                $modelSource = $source->one();
                if ($modelSource && !empty($modelSource[$this->attribute])) {
                    $str = "
                    <div class='box-language text-secondary'>
                    <div class='text-language'>
                        <small><strong>".\Yii::t('app', 'Testo sorgente')."</strong></small><br>
                        ".$modelSource[$this->attribute]." 
                        </div>
                    </div>
        ";
                }
            }
        }
        return $str;
    }
}