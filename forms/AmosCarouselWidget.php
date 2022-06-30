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
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\interfaces\BaseContentModelInterface;
use open20\amos\core\interfaces\ContentModelInterface;
use open20\amos\core\interfaces\ModelGrammarInterface;
use open20\amos\core\interfaces\ModelImageInterface;
use open20\amos\core\interfaces\ModelLabelsInterface;
use open20\amos\core\interfaces\ViewModelInterface;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\record\Record;
use Yii;
use yii\base\Widget;
use yii\bootstrap\Carousel;
use yii\helpers\ArrayHelper;

/**
 * Class AmosCarouselWidget
 * @package open20\amos\core\forms
 */
class AmosCarouselWidget extends Widget
{
    /**
     * @var array $items
     */
    private $items = null;

    /**
     * @var string $singleItemView The view for a single carousel item.
     */
    public $singleItemView = "@vendor/open20/amos-core/forms/views/widgets/amos_carousel_widget_item";

    /**
     * @var string $carouselId
     */
    public $carouselId = 'carouselWidget';

    /**
     * @var bool $showIndicators
     */
    public $showIndicators = true;

    /**
     * @var bool $showControls
     */
    public $showControls = true;

    /**
     * @var array $controls
     */
    public $controls = [];

    /**
     * @var string $additionalCarouselClass
     */
    public $additionalCarouselClass = '';

    /**
     * @var array $additionalOptions
     */
    public $additionalOptions = [];

    /**
     * @var array $carouselItems
     */
    private $carouselItems = null;

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

        $this->controls = [AmosIcons::show('chevron-left'), AmosIcons::show('chevron-right')];

        if (is_null($this->items)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#widget_message_null_param', ['class' => basename(__CLASS__), 'param' => 'items']));
        }

        if (!is_array($this->items)) {
            throw new AmosException(BaseAmosModule::t('amoscore', '#widget_message_not_array', ['class' => basename(__CLASS__), 'param' => 'items']));
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (Yii::$app->getModule('layout')) {
            \open20\amos\layout\assets\CarouselAsset::register($this->view);
        } else {
            throw new AmosException(BaseAmosModule::t('amoscore', '#amos_carousel_widget_missing_layout_module'));
        }

        $countItems = $this->composeCarouselItems();
        if ($countItems === 0) {
            return '';
        }

        return Carousel::widget([
            'items' => $this->carouselItems,
            'controls' => (($this->showControls && ($countItems > 1)) ? $this->controls : false),
            'showIndicators' => ($this->showIndicators && ($countItems > 1)),
            'options' => $this->composeCarouselOptions()
        ]);
    }

    /**
     * This method take the models present in the widget variable "items" and transform these models in carousel items.
     * @return bool
     */
    protected function composeCarouselItems()
    {
        foreach ($this->items as $model) {
            /** @var Record $model */
            $this->carouselItems[] = $this->render($this->singleItemView, ['model' => $model]);
        }
        
        return ($this->carouselItems != null)
          ? count($this->carouselItems)
          : 0
        ;
    }

    /**
     * This method make the carousel options.
     * @return array
     */
    protected function composeCarouselOptions()
    {
        $defaultOptions = [
            'id' => $this->carouselId,
            'class' => 'slide carousel-evidence' . ($this->additionalCarouselClass ? ' ' . $this->additionalCarouselClass : ''),
            'data' => [
                'ride' => 'carousel',
            ]
        ];
        $options = ArrayHelper::merge($this->additionalOptions, $defaultOptions);
        return $options;
    }

    /**
     * This static method returns the content model image url.
     * @param Record $model
     * @return string
     */
    public static function getContentImageUrl($model)
    {
        $defaultUrl = '/img/img_default.jpg';
        $url = $defaultUrl;
        if ($model instanceof ModelImageInterface) {
            $url = $model->getModelImageUrl('square_medium', true, $defaultUrl, false, true);
        }
        return $url;
    }

    /**
     * This static method returns the content model image alt.
     * @param Record $model
     * @return string
     */
    public static function getContentImageAlt($model)
    {
        $imageAlt = BaseAmosModule::t('amoscore', '#amos_carousel_widget_default_content_image_alt');
        if ($model instanceof ModelLabelsInterface) {
            $modelGrammar = $model->getGrammar();
            if ($modelGrammar instanceof ModelGrammarInterface) {
                $imageAlt = BaseAmosModule::t('amoscore', '#amos_carousel_widget_image_of') . $modelGrammar->getArticleSingular() . ' ' . strtolower($modelGrammar->getModelSingularLabel());
            }
        }
        return $imageAlt;
    }

    /**
     * This static method returns the content model view url.
     * @param Record $model
     * @return string
     */
    public static function getContentViewUrl($model)
    {
        $contentViewUrl = '#';
        if ($model instanceof ViewModelInterface) {
            $contentViewUrl = $model->getFullViewUrl();
        }
        return $contentViewUrl;
    }

    /**
     * This static method returns the content model title.
     * @param Record $model
     * @return string
     */
    public static function getContentTitle($model)
    {
        $contentTitle = BaseAmosModule::t('amoscore', '#amos_carousel_widget_default_content_title');
        if ($model instanceof BaseContentModelInterface) {
            $contentTitle = $model->getTitle();
        }
        return $contentTitle;
    }

    /**
     * This static method returns the content model short description.
     * @param Record $model
     * @return string
     */
    public static function getContentShortDescription($model)
    {
        $contentShortDescription = BaseAmosModule::t('amoscore', '#amos_carousel_widget_default_content_short_description');
        if ($model instanceof BaseContentModelInterface) {
            $contentShortDescription = $model->getShortDescription(true);
        }
        return $contentShortDescription;
    }

    /**
     * This static method returns the content model read all link title.
     * @param Record $model
     * @return string
     */
    public static function getContentReadAllLinkTitle($model)
    {
        $linkTitle = BaseAmosModule::t('amoscore', '#amos_carousel_widget_read_all');
        if ($model instanceof ModelLabelsInterface) {
            $modelGrammar = $model->getGrammar();
            if ($modelGrammar instanceof ModelGrammarInterface) {
                $linkTitle = BaseAmosModule::t('amoscore', '#amos_carousel_widget_go_to_content') . $modelGrammar->getArticleSingular() . ' ' . strtolower($modelGrammar->getModelSingularLabel());
            }
        }
        return $linkTitle;
    }

    /**
     * This static method returns the content model date array.
     * @param Record $model
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public static function getContentDateArray($model)
    {
        $contentDateArray = [];
        if ($model instanceof ContentModelInterface) {
            $publishedFrom = $model->getPublicatedFrom();
            if ($publishedFrom) {
                $data = \Yii::$app->getFormatter()->asDate($publishedFrom, 'long');
                $arrayData = explode(' ', $data);
                $contentDateArray['day'] = $arrayData[0];
                $contentDateArray['month'] = $arrayData[1];
                $contentDateArray['year'] = $arrayData[2];
            }
        }
        return $contentDateArray;
    }
}
