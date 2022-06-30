<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views\grid
 * @category   CategoryName
 */

namespace open20\amos\core\views\grid;

use Yii;
use yii\grid\ActionColumn as YiiActionColumn;
use yii\helpers\Html;

/**
 * Class ActionColumn
 * @package open20\amos\core\views\grid
 */
class ActionColumn extends YiiActionColumn
{
    /**
     * @var string $buttonClass The class of a single action columns button
     */
    public $buttonClass = 'open20\amos\core\views\common\Buttons';

    /**
     * @var array $viewOptions The "view" button options
     */
    public $viewOptions = [
        'class' => 'btn btn-tools-secondary' //old bk-btnMore
    ];

    /**
     * @var array $updateOptions The "update" button options
     */
    public $updateOptions = [
        'class' => 'btn btn-tools-secondary' //old bk-btnEdit
    ];

    /**
     * @var array $deleteOptions The "delete" button options
     */
    public $deleteOptions = [
        'class' => 'btn btn-danger-inverse' //old bk-btnDelete
    ];

    /**
     * @var bool $_isDropdown
     */
    public $_isDropdown = false;

    /**
     * @var array $additionalParams
     */
    public $additionalParams = [];

    /**
     * @var bool $useOnly_additionalParams
     */
    public $useOnly_additionalParams = false;
    
    /**
     * @var \Closure|null $beforeRenderParent
     */
    public $beforeRenderParent = null;
    
    /**
     * @var \Closure|null $afterRenderParent
     */
    public $afterRenderParent = null;

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        //if isset the additional params parameter
        if (!empty($this->additionalParams)) {
            //if isset to use only additional params
            if ($this->useOnly_additionalParams) {
                $key = $this->additionalParams;
            } else {
                if (is_array($this->additionalParams)) {
                    //usually $key isn't an array: it contains the record id only ad integer value.
                    if (is_array($key)) {
                        $key = array_merge($key, $this->additionalParams);
                    } else {
                        $tmp_array_key = ['id' => $key];
                        $key = array_merge($tmp_array_key, $this->additionalParams);
                    }
                } else {
                    //error: additional params MUST BE array key => value
                }
            }
        }
    
        if (!is_null($this->beforeRenderParent) && ($this->beforeRenderParent instanceof \Closure)) {
            $beforeRenderParentRes = call_user_func($this->beforeRenderParent, $model, $key, $index);
            if (is_array($key)) {
                $key['beforeRenderParentRes'] = $beforeRenderParentRes;
            } else {
                $tmp_array_key = ['id' => $key];
                $key = array_merge($tmp_array_key, ['beforeRenderParentRes' => $beforeRenderParentRes]);
            }
        }
        
        $renderDataCellContent = preg_replace_callback('/\\{([\w\-\/]+)\\}/',
            function ($matches) use ($model, $key, $index) {
            $name = $matches[1];

            if (isset($this->visibleButtons[$name])) {
                $isVisible = $this->visibleButtons[$name] instanceof \Closure ? call_user_func($this->visibleButtons[$name],
                        $model, $key, $index) : $this->visibleButtons[$name];
            } else {
                $isVisible = true;
            }

            if ($isVisible && isset($this->buttons[$name])) {
                if ($name == 'view' && ((!empty($model->usePrettyUrl) && $model->usePrettyUrl == true) || (!empty($model->useFrontendView)
                    && $model->useFrontendView == true) )) {

                    $url = $model->getFullViewUrl();
                } else {
                    $url = $this->createUrl($name, $model, $key, $index);
                }
                return call_user_func($this->buttons[$name], $url, $model, $key);
            }

            return '';
        }, $this->template);

        if (!is_null($this->afterRenderParent) && ($this->afterRenderParent instanceof \Closure)) {
            call_user_func($this->afterRenderParent, $model, $key, $index);
        }
        
        return Html::tag('div', $renderDataCellContent, ['class' => 'bk-elementActions container-action']) . Html::tag('div', '', ['class' => 'clearfix']);
    }

    /**
     * @inheritdoc
     */
    protected function initDefaultButtons()
    {
        $buttonOptions = [
            'class' => $this->buttonClass,
            'template' => $this->template,
            '_isDropdown' => $this->_isDropdown,
            'viewOptions' => $this->viewOptions,
            'updateOptions' => $this->updateOptions,
            'deleteOptions' => $this->deleteOptions,
            'buttons' => $this->buttons,
        ];

        $button = Yii::createObject($buttonOptions);
        $button->initDefaultButtons();
        $this->buttons = $button->buttons;
    }
}
