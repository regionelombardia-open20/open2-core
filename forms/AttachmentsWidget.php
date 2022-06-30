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

use open20\amos\attachments\components\AttachmentsInput;
use open20\amos\attachments\components\AttachmentsList;
use open20\amos\core\exceptions\AmosException;
use open20\amos\core\helpers\Html;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\record\Record;
use yii\base\Widget;

/**
 * Class AttachmentsWidget
 * @package open20\amos\core\forms
 */
class AttachmentsWidget extends Widget
{
    /**
     * @var string $layout
     */
    public $layout = '{beginContainer}{title}{attachmentsInput}{attachmentsList}{endContainer}';

    /**
     * @var ActiveForm $form
     */
    private $form;

    /**
     * @var Record $model
     */
    private $model;

    /**
     * @var string $modelField
     */
    private $modelField;

    /**
     * @var array $containerOptions
     */
    private $containerOptions = [
        'class' => 'col-xs-12 attachment-section nop'
    ];

    /**
     * @var array $attachInputOptions
     */
    private $attachInputOptions = [
        'multiple' => false
    ];

    /**
     * @var array $attachInputPluginOptions
     */
    private $attachInputPluginOptions = [
        'maxFileCount' => 1,
        'showPreview' => false
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (is_null($this->form)) {
            throw new AmosException(BaseAmosModule::t('amoscore', 'AttachmentsWidget: missing form'));
        }

        if (is_null($this->model)) {
            throw new AmosException(BaseAmosModule::t('amoscore', 'AttachmentsWidget: missing model'));
        }

        if (is_null($this->modelField)) {
            throw new AmosException(BaseAmosModule::t('amoscore', 'AttachmentsWidget: missing modelField'));
        }

        if (!$this->form instanceof ActiveForm) {
            throw new AmosException(BaseAmosModule::t('amoscore', 'AttachmentsWidget: form is not an ActiveForm object'));
        }

        if (!$this->model instanceof Record) {
            throw new AmosException(BaseAmosModule::t('amoscore', 'AttachmentsWidget: model is not a Record object'));
        }

        if (!is_array($this->attachInputOptions)) {
            throw new AmosException(BaseAmosModule::t('amoscore', 'AttachmentsWidget: attachInputOptions is not an array'));
        }

        if (!is_array($this->attachInputPluginOptions)) {
            throw new AmosException(BaseAmosModule::t('amoscore', 'AttachmentsWidget: attachInputPluginOptions is not an array'));
        }
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
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
    public function getModelField()
    {
        return $this->modelField;
    }

    /**
     * @param string $modelField
     */
    public function setModelField($modelField)
    {
        $this->modelField = $modelField;
    }

    /**
     * @return array
     */
    public function getContainerOptions()
    {
        return $this->containerOptions;
    }

    /**
     * @param array $containerOptions
     */
    public function setContainerOptions($containerOptions)
    {
        $this->containerOptions = $containerOptions;
    }

    /**
     * @return array
     */
    public function getAttachInputOptions()
    {
        return $this->attachInputOptions;
    }

    /**
     * @param array $attachInputOptions
     */
    public function setAttachInputOptions($attachInputOptions)
    {
        $this->attachInputOptions = $attachInputOptions;
    }

    /**
     * @return array
     */
    public function getAttachInputPluginOptions()
    {
        return $this->attachInputPluginOptions;
    }

    /**
     * @param array $attachInputPluginOptions
     */
    public function setAttachInputPluginOptions($attachInputPluginOptions)
    {
        $this->attachInputPluginOptions = $attachInputPluginOptions;
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
     * @throws \Exception
     */
    protected function renderSection($name)
    {
        switch ($name) {
            case '{beginContainer}':
                return $this->renderBeginContainer();
            case '{title}':
                return $this->renderTitle();
            case '{attachmentsInput}':
                return $this->renderAttachmentsInput();
            case '{attachmentsList}':
                return $this->renderAttachmentsList();
            case '{endContainer}':
                return $this->renderEndContainer();
            default:
                return false;
        }
    }

    /**
     * Render the begin of overall containers.
     * @return string
     */
    protected function renderBeginContainer()
    {
        $html = Html::beginTag('div', $this->containerOptions);
        $html .= Html::beginTag('div', ['class' => 'col-xs-12']);
        return $html;
    }

    /**
     * Render the title.
     * @return string
     */
    protected function renderTitle()
    {
        $title = Html::tag('h2', BaseAmosModule::t('amoscore', '#attachments_title'));
        return $title;
    }

    /**
     * Render the attachments input.
     * @return string
     */
    protected function renderAttachmentsInput()
    {
        $html = $this->form->field($this->model, $this->modelField)->widget(AttachmentsInput::classname(), [
            'options' => $this->attachInputOptions,
            'pluginOptions' => $this->attachInputPluginOptions
        ])->label(BaseAmosModule::t('amoscore', '#attachments_field'))->hint(BaseAmosModule::t('amoscore', '#attachments_field_hint'));
        return $html;
    }

    /**
     * Render the attachments list.
     * @return string
     * @throws \Exception
     */
    protected function renderAttachmentsList()
    {
        $html = AttachmentsList::widget([
            'model' => $this->model,
            'attribute' => $this->modelField
        ]);
        return $html;
    }

    /**
     * Render the end of overall containers.
     * @return string
     */
    protected function renderEndContainer()
    {
        $html = Html::endTag('div');
        $html .= Html::endTag('div');
        return $html;
    }
}
