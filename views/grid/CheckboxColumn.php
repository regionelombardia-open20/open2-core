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

use Closure;
use yii\grid\CheckboxColumn as YiiCheckboxColumn;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;

/**
 * CheckboxColumn displays a column of checkboxes in a grid view.
 *
 * To add a CheckboxColumn to the [[GridView]], add it to the [[GridView::columns|columns]] configuration as follows:
 *
 * ```php
 * 'columns' => [
 *     // ...
 *     [
 *         'class' => 'lispa\amos\core\views\grid\CheckboxColumn',
 * 'name' => 'amosWidgetsClassnames[]',
 * 'checkboxOptions' => function($model, $key, $index, $column){
 * return [
 * 'id' => \yii\helpers\StringHelper::basename($model['classname']),
 * 'value' => $model['classname'],
 * 'checked' => in_array($model['classname'], $this->params['widgetSelected'])
 * ];
 *                  },
 *         // you may configure additional properties here
 *     ],
 * ]
 * ```
 *
 * Users may click on the checkboxes to select rows of the grid. The selected rows may be
 * obtained by calling the following JavaScript code:
 *
 * ```javascript
 * var keys = $('#grid').yiiGridView('getSelectedRows');
 * // keys is an array consisting of the keys associated with the selected rows
 * ```
 *
 */
class CheckboxColumn extends YiiCheckboxColumn
{
//    /**
//     * @var string $containerGridId
//     */
//    public $containerGridId = '';

    /**
     * @inheritdoc
     */
    protected function renderHeaderCellContent()
    {
        if ($this->header !== null || !$this->multiple) {
            return parent::renderHeaderCellContent();
        } else {
            $idGrid = (!empty($this->grid->getId()) ? $this->grid->id : $this->getHeaderCheckBoxName());
            return '<div class="checkbox"><label for="id_header-' . $idGrid . '"><span class="sr-only">' . \Yii::t('amoscore', 'Select All') . '</span>' . Html::checkbox($this->getHeaderCheckBoxName(), false, ['class' => 'select-on-check-all', 'id' => 'id_header-' . $idGrid]) . '</label></div>';
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->checkboxOptions instanceof Closure) {
            $options = call_user_func($this->checkboxOptions, $model, $key, $index, $this);
        } else {
            $options = $this->checkboxOptions;
        }

        if (!isset($options['value'])) {
            $options['value'] = is_array($key) ? Json::encode($key) : $key;
        }

        if ($this->cssClass !== null) {
            Html::addCssClass($options, $this->cssClass);
        }
        return '<div class="checkbox"><label for="' . ((isset($options['id'])) ? $options['id'] : 'id-mancante') . '"><span class="sr-only">' . str_replace(['[', ']'], '', Inflector::camel2words($this->name)) . '</span>' . Html::checkbox($this->name, !empty($options['checked']), $options) . '</label></div>';
    }

//    /**
//     * @inheritdoc
//     */
//    public function registerClientScript()
//    {
//        $oldGridId = $this->grid->options['id'];
//        if (is_string($this->containerGridId) && (strlen($this->containerGridId) > 0)) {
//            $this->grid->options['id'] = $this->containerGridId;
//        }
//        parent::registerClientScript();
//        $this->grid->options['id'] = $oldGridId;
//    }
}
