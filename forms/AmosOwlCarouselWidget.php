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

use open20\amos\core\exceptions\AmosException;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\record\Record;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

/**
 * Class AmosOwlCarouselWidget
 * @package open20\amos\core\forms
 */
class AmosOwlCarouselWidget extends Widget
{
    /**
     * @var array $items
     */
    private $items = null;

    /**
     * @var string $widgetView The widget view.
     */
    protected $widgetView = "@vendor/open20/amos-core/forms/views/widgets/amos_owl_carousel_widget";

    /**
     * @var string $singleItemView The view for a single owl carousel item.
     */
    public $singleItemView = "";

    /**
     * @var string $owlCarouselId
     */
    public $owlCarouselId = 'owlCarouselWidget';

    /**
     * @var string $owlCarouselClass
     */
    public $owlCarouselClass = 'owl-carousel-class';

    /**
     * @var string $owlCarouselJSOptions
     */
    public $owlCarouselJSOptions = "{
        margin: 10,
        nav: true,
        loop: false,
        autoplay: false
    }";

    /**
     * @var array $additionalOptions
     */
    public $additionalOptions = [];

    /**
     * @var array $owlCarouselContent
     */
    private $owlCarouselContent = '';

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $items
     * @throws AmosException
     */
    public function setItems($items)
    {
        if (!is_array($items)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#widget_message_not_array', ['class' => basename(__CLASS__), 'param' => 'items']));
        }
        $this->items = $items;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (is_null($this->items)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#widget_message_null_param', ['class' => basename(__CLASS__), 'param' => 'items']));
        }

        if (!is_array($this->items)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#widget_message_not_array', ['class' => basename(__CLASS__), 'param' => 'items']));
        }

        if (!is_string($this->singleItemView)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#widget_message_not_string', ['class' => basename(__CLASS__), 'param' => 'singleItemView']));
        }

        if (empty($this->singleItemView)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#widget_message_empty_string', ['class' => basename(__CLASS__), 'param' => 'singleItemView']));
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->getModule('layout')) {
            \open20\amos\layout\assets\OwlCarouselAsset::register($this->view);
        } else {
            throw new AmosException(BaseAmosModule::t('amoscore', '#amos_carousel_widget_missing_layout_module'));
        }

        if (!$this->composeOwlCarouselItems()) {
            return '';
        }

        return $this->render($this->widgetView, [
            'widget' => $this,
            'owlCarouselContent' => $this->owlCarouselContent,
            'containerOptions' => $this->composeOwlCarouselOptions(),
        ]);
    }

    /**
     * This method take the models present in the widget variable "items" and transform these models in carousel items.
     * @return bool
     */
    protected function composeOwlCarouselItems()
    {
        foreach ($this->items as $model) {
            /** @var Record $model */
            $this->owlCarouselContent .= $this->render($this->singleItemView, ['model' => $model, 'widget' => $this]);
        }
        return (!empty($this->owlCarouselContent));
    }

    /**
     * This method make the owl carousel options.
     * @return array
     */
    protected function composeOwlCarouselOptions()
    {
        $defaultOptions = [
            'id' => $this->owlCarouselId,
            'class' => $this->owlCarouselClass,
        ];
        $options = ArrayHelper::merge($this->additionalOptions, $defaultOptions);
        return $options;
    }
}
