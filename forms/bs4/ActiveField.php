<?php

/**
 */

namespace open20\amos\core\forms\bs4;

use open20\amos\core\helpers\Html;
use open20\amos\core\helpers\StringHelper;
use open20\amos\core\module\BaseAmosModule;
use yii\helpers\ArrayHelper;

/**
 * A Bootstrap 4 enhanced version of [[\yii\widgets\ActiveField]].
 *
 * This class adds some useful features to [[\yii\widgets\ActiveField|ActiveField]] to render all
 * sorts of Bootstrap 4 form fields in different form layouts:
 *
 * - [[inputTemplate]] is an optional template to render complex inputs, for example input groups
 * - [[horizontalCssClasses]] defines the CSS grid classes to add to label, wrapper, error and hint
 *   in horizontal forms
 * - [[inline]]/[[inline()]] is used to render inline [[checkboxList()]] and [[radioList()]]
 * - [[enableError]] can be set to `false` to disable to the error
 * - [[enableLabel]] can be set to `false` to disable to the label
 * - [[label()]] can be used with a `bool` argument to enable/disable the label
 *
 * There are also some new placeholders that you can use in the [[template]] configuration:
 *
 * - `{beginLabel}`: the opening label tag
 * - `{labelTitle}`: the label title for use with `{beginLabel}`/`{endLabel}`
 * - `{endLabel}`: the closing label tag
 * - `{beginWrapper}`: the opening wrapper tag
 * - `{endWrapper}`: the closing wrapper tag
 *
 * The wrapper tag is only used for some layouts and form elements.
 *
 * Note that some elements use slightly different defaults for [[template]] and other options.
 * You may want to override those predefined templates for checkboxes, radio buttons, checkboxLists
 * and radioLists in the [[\yii\widgets\ActiveForm::fieldConfig|fieldConfig]] of the
 * [[\yii\widgets\ActiveForm]]:
 *
 * - [[checkTemplate]] the default template for checkboxes and radios
 * - [[radioTemplate]] the template for radio buttons in default layout
 * - [[checkHorizontalTemplate]] the template for checkboxes in horizontal layout
 * - [[radioHorizontalTemplate]] the template for radio buttons in horizontal layout
 * - [[checkEnclosedTemplate]] the template for checkboxes and radios enclosed by label
 *
 * Example:
 *
 * ```php
 * use yii\bootstrap4\ActiveForm;
 *
 * $form = ActiveForm::begin(['layout' => 'horizontal']);
 *
 * // Form field without label
 * echo $form->field($model, 'demo', [
 *     'inputOptions' => [
 *         'placeholder' => $model->getAttributeLabel('demo'),
 *     ],
 * ])->label(false);
 *
 * // Inline radio list
 * echo $form->field($model, 'demo')->inline()->radioList($items);
 *
 * // Control sizing in horizontal mode
 * echo $form->field($model, 'demo', [
 *     'horizontalCssClasses' => [
 *         'wrapper' => 'col-sm-2',
 *     ]
 * ]);
 *
 * // With 'default' layout you would use 'template' to size a specific field:
 * echo $form->field($model, 'demo', [
 *     'template' => '{label} <div class="row"><div class="col-sm-4">{input}{error}{hint}</div></div>'
 * ]);
 *
 * // Input group
 * echo $form->field($model, 'demo', [
 *     'inputTemplate' => '<div class="input-group"><div class="input-group-prepend">
 *         <span class="input-group-text">@</span>
 *     </div>{input}</div>',
 * ]);
 *
 * ActiveForm::end();
 * ```
 *
 *
 */
class ActiveField extends \yii\bootstrap4\ActiveField
{

    private $labelTranslationField;

    /**
     * {@inheritdoc}
     */
    public function label($label = null, $options = [])
    {
        $this->setTemplateByTranslation();
        if (is_bool($label)) {
            $this->enableLabel = $label;
            if ($label === false && $this->form->layout === ActiveForm::LAYOUT_HORIZONTAL) {
                Html::addCssClass($this->wrapperOptions, $this->horizontalCssClasses['offset']);
            }
        } else {

            $this->enableLabel = true;
            $this->renderLabelParts($label, $options);

            parent::label($label, $options);
        }
        return $this;
    }

    /**
     * @param string|null $label the label or null to use model label
     * @param array $options the tag options
     */
    //    protected function renderLabelParts($label = null, $options = [])
    //    {
    //        $options = array_merge($this->labelOptions, $options);
    //        if ($label === null) {
    //            if (isset($options['label'])) {
    //                $label = $options['label'];
    //                unset($options['label']);
    //            } else {
    //                $attribute = Html::getAttributeName($this->attribute);
    //                $label = Html::encode($this->model->getAttributeLabel($attribute));
    //            }
    //        }
    //
    //        if (!isset($options['for'])) {
    //            $options['for'] = Html::getInputId($this->model, $this->attribute);
    //        }
    //        $this->parts['{beginLabel}'] = Html::beginTag('label', $options);
    //        $this->parts['{endLabel}'] = Html::endTag('label');
    //        if (!isset($this->parts['{labelTitle}'])) {
    //            $this->parts['{labelTitle}'] = $label;
    //        }
    //    }

    /**
     *
     */
    public function setTemplateByTranslation()
    {
        try {
            if ($this->checkCanShowTranslationInLine()) {
                $module = \Yii::$app->getModule('translation');
                if (empty($this->labelTranslationField) && !empty($module)) {
                    if (
                        !empty($module->enableLabelTranslationField) && $module->enableLabelTranslationField === true && $module->byPassPermissionInlineTranslation
                        === true
                    ) {
                        $configuration = $module->translationBootstrap;
                        if (!empty($configuration['configuration']['translationContents'])) {
                            $translationContents = $configuration['configuration']['translationContents'];
                            if (!empty($translationContents['classBehavior']) && !empty($translationContents['models'])) {
                                foreach ($translationContents['models'] as $model) {
                                    if (!empty($model['namespace']) && !empty($model['attributes'])) {
                                        $classSender = get_class($this->model);
                                        if ($classSender == $model['namespace'] && !empty($model['attributes']) && in_array(
                                            $this->attribute,
                                            $model['attributes']
                                        )) {
                                            if (!empty($module->labelTranslationField)) {
                                                eval("\$translationLabelAltField = {$module->translationLabelAltField}");
                                                eval("\$translationLabelField = {$module->translationLabelField}");
                                                $templateTranslationField = $module->templateTranslationField;
                                                $templateTranslationAltField = $module->templateTranslationAltField;
                                                $this->labelTranslationField = str_replace(
                                                    $templateTranslationAltField,
                                                    $translationLabelAltField,
                                                    $module->labelTranslationField
                                                );
                                                $this->labelTranslationField = str_replace(
                                                    $templateTranslationField,
                                                    $translationLabelField,
                                                    $this->labelTranslationField
                                                );
                                            } else {
                                                $this->labelTranslationField = ' (<span class="label_translation am am-translate" title="' . BaseAmosModule::t(
                                                    "amostranslation",
                                                    "Testo traducibile direttamente scrivendo in questo campo, tradurrai nella lingua selezionata, la visualizzazione attuale è in"
                                                ) . ' ' . strtoupper(substr(
                                                    \Yii::$app->language,
                                                    0,
                                                    2
                                                )) . '"> - ' . strtoupper(substr(\Yii::$app->language, 0, 2)) . '</span>)';
                                            }
                                            if (!empty($this->labelTranslationField)) {
                                                $textSource = $this->translationSource($this->model->id, $module, $model['namespace']);
                                                $posLabel = strpos($this->template, '{label}');
                                                if ($posLabel !== false && strpos($this->template, $this->labelTranslationField) === false) {
                                                    $pos = $posLabel + 7;
                                                    $this->template = $textSource . substr($this->template, 0, $pos) . $this->labelTranslationField . substr(
                                                        $this->template,
                                                        $pos
                                                    );
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
            $class = \Yii::$app->params['showTranslationInlinePersonalized']['class'];
            $method = \Yii::$app->params['showTranslationInlinePersonalized']['method'];
            $okMultilanguage = $class::$method();
            return $okMultilanguage;
        }

        return true;
    }


    public function translationSource($id, $module, $namespace)
    {
        $str = '';
        if (!$this->model->isNewRecord) {
            $lang = \Yii::$app->language;
            if ($lang != 'it-IT') {
                $classNameTrans = $module->modelNs . '\\' . StringHelper::basename($namespace) . "Translation";
                $language_source = null;
                $language_source = \Yii::$app->request->getQueryParam(StringHelper::basename($classNameTrans))['language_source'];
                list($language_source_res, $source) = \open20\amos\translation\models\TranslationConf::getSource($language_source, $id, $lang, $namespace);

                $modelSource = $source->one();
                if ($modelSource && !empty($modelSource[$this->attribute])) {
                    $str = "
            <div class='box-language text-secondary'>
            <div class='text-language'>
                <small><strong>" . \Yii::t('app', 'Testo sorgente') . "</strong></small><br>
                " . $modelSource[$this->attribute] . " 
                </div>
            </div>
        ";
                }
            }
        }
        return $str;
    }
}
