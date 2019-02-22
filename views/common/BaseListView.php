<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views\common
 * @category   CategoryName
 */

namespace lispa\amos\core\views\common;

use lispa\amos\core\views\toolbars\StatsToolbar;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ListView;
use yii\web\View;

class BaseListView extends ListView
{
    public $showItemToolbar       = true;
    public $buttonObj;
    public $template              = '{view} {update} {delete}';
    public $containerOptions      = [
    ];
    public $itemsContainerTag     = 'div';
    public $itemsContainerOptions = [
        'class' => "",
        "role" => "listbox",
        "data-role" => "list-view"
    ];
    public $buttons;
    public $buttonClass           = 'lispa\amos\core\views\common\Buttons';
    public $viewOptions           = [
        'class' => 'bk-btnMore'
    ];
    public $updateOptions         = [
        'class' => 'bk-btnEdit'
    ];
    public $deleteOptions         = [
        'class' => 'bk-btnDelete'
    ];
    public $_isDropdown           = false;

    /**
     * Abilita o disabilita il plugin masonry
     * @var boolean
     */
    public $masonry = false;

    /**
     * Array con i parametri da passare a masonry, vedere http://masonry.desandro.com/
     * @var array
     */
    public $masonryOptions = [];

    /**
     * Il selettore del contenitore degli item che dovrà gestire masonry
     * @var string
     */
    public $masonrySelector = '.grid';

    public function init()
    {
        parent::init();
        $this->initDefaultButtons();
    }

    protected function initDefaultButtons()
    {

        $buttonOptions = [
            'class' => $this->buttonClass,
            'template' => $this->template,
            '_isDropdown' => $this->_isDropdown,
            'viewOptions' => $this->viewOptions,
            'updateOptions' => $this->updateOptions,
            'deleteOptions' => $this->deleteOptions,
        ];

        $this->buttonObj = Yii::createObject($buttonOptions);
        $this->buttonObj->initDefaultButtons();
        $this->buttons   = $this->buttonObj->buttons;
    }

    /**
     * Renders all data models.
     * @return string the rendering result
     */
    public function renderItems()
    {
        $models  = $this->dataProvider->getModels();
        $keys    = $this->dataProvider->getKeys();
        $content = [];
        foreach (array_values($models) as $index => $model) {
            $content[] = $this->renderItem($model, $keys[$index], $index);
        }
        if ($this->masonry) {
            $this->itemsContainerOptions['class'] = str_replace(['.', '#'], '', $this->masonrySelector);
            $masonryColumnWidthClass              = (isset($this->itemsContainerOptions['columnWidth'])) ? str_replace('.',
                    '', $this->itemsContainerOptions['columnWidth']) : 'grid-sizer';
            $itemsHtml                            = Html::tag($this->itemsContainerTag,
                    Html::tag('div', '', ['class' => $masonryColumnWidthClass]).implode("\n", $content),
                    $this->itemsContainerOptions);
        } else {
            $itemsHtml = Html::tag($this->itemsContainerTag, implode("\n", $content), $this->itemsContainerOptions);
        }

        return Html::tag('div', $itemsHtml, $this->containerOptions);
    }

    /**
     * Renders a single data model.
     * @param mixed $model the data model to be rendered
     * @param mixed $key the key value associated with the data model
     * @param integer $index the zero-based index of the data model in the model array returned by [[dataProvider]].
     * @return string the rendering result
     */
    public function renderItem($model, $key, $index)
    {
        if ($this->itemView === null) {
            $content = $key;
        } elseif (is_string($this->itemView)) {
            $content = $this->getView()->render($this->itemView,
                array_merge([
                'model' => $model,
                'key' => $key,
                'index' => $index,
                'widget' => $this,
                'buttons' => $this->buttonObj->renderButtonsContent($model, $key, $index),
                'statsToolbar' => $this->showItemToolbar
                    ], $this->viewParams));
        } else {
            $content = call_user_func($this->itemView, $model, $key, $index, $this);
        }

        $options = $this->itemOptions;
        //$tag = ArrayHelper::remove($options, 'tag', 'div');
        return Html::tag('div', $content, $options);
    }

    public function run()
    {
        if ($this->showOnEmpty || $this->dataProvider->getCount() > 0) {
            $content = preg_replace_callback("/{\\w+}/",
                function ($matches) {
                $content = $this->renderSection($matches[0]);

                return $content === false ? $matches[0] : $content;
            }, $this->layout);
        } else {
            $content = $this->renderEmpty();
        }

        $options = $this->options;
        $tag     = ArrayHelper::remove($options, 'tag', 'div');
        echo Html::tag($tag, $content, $options);

        if ($this->masonry) {
            $this->initMasonry();
        }
    }

    protected function initMasonry()
    {
        $view    = \Yii::$app->getView();
        $moduleL = \Yii::$app->getModule('layout');
        if (!empty($moduleL)) {
            \lispa\amos\layout\assets\MasonryAsset::register($view);
        } else {
            \lispa\amos\core\views\assets\MasonryAsset::register($view);
        }
        $options = \yii\helpers\Json::encode($this->masonryOptions);
        $view->registerJs("$('$this->masonrySelector').masonry($options);", View::POS_READY);
    }
}