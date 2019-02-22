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

use kartik\base\Config;
use kartik\grid\DataColumn as KartikDataColumn;
use kartik\grid\GridView;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\db\ActiveQueryInterface;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * DataColumn is the default column type for the [[GridView]] widget.
 *
 * It is used to show data columns and allows [[enableSorting|sorting]] and [[filter|filtering]] them.
 *
 * A simple data column definition refers to an attribute in the data model of the
 * GridView's data provider. The name of the attribute is specified by [[attribute]].
 *
 * By setting [[value]] and [[label]], the header and cell content can be customized.
 *
 * A data column differentiates between the [[getDataCellValue|data cell value]] and the
 * [[renderDataCellContent|data cell content]]. The cell value is an un-formatted value that
 * may be used for calculation, while the actual cell content is a [[format|formatted]] version of that
 * value which may contain HTML markup.
 *
 */
class DataColumn extends KartikDataColumn
{

    /**
     * @inheritdoc
     */
    protected function renderFilterCellContent()
    {
        if (is_string($this->filter)) {
            return $this->filter;
        }

        $model = $this->grid->filterModel;

        $content = parent::renderFilterCellContent();

        $widgetClass = $this->filterType;
        $options = [
            'model' => $this->grid->filterModel,
            'attribute' => $this->attribute,
            'options' => $this->filterInputOptions
        ];
        $chkType = !empty($this->filterType) && $this->filterType !== GridView::FILTER_CHECKBOX &&
            $this->filterType !== GridView::FILTER_RADIO && !class_exists($this->filterType);

        if ($this->filter === false || empty($this->filterType) || $content === $this->grid->emptyCell || $chkType) {

            if ($this->filter !== false && $model instanceof Model && $this->attribute !== null && $model->isAttributeActive($this->attribute)) {
                if ($model->hasErrors($this->attribute)) {
                    Html::addCssClass($this->filterOptions, 'has-error');
                    $error = ' ' . Html::error($model, $this->attribute, $this->grid->filterErrorOptions);
                } else {
                    $error = '';
                }
                if (is_array($this->filter)) {

                } else {
                    if (empty($this->filterInputOptions['id'])) {
                        $this->filterInputOptions['id'] = $this->attribute . '-filter_input';
                    }
                    if ($this->filterType === GridView::FILTER_CHECKBOX) {
                        return Html::activeCheckbox($this->grid->filterModel, $this->attribute, $this->filterInputOptions);
                    }
                    $options = array_replace_recursive($this->filterWidgetOptions, $options);
                    return '<label for="' . $this->filterInputOptions['id'] . '" class="sr-only">' . $this->attribute . '</label>' . Html::activeTextInput($model, $this->attribute, $this->filterInputOptions) . $error;
                }
            } else {
                return $content;
            }
        }

        if (is_array($this->filter)) {
            if (Config::isInputWidget($this->filterType) && $this->grid->pjax) {
                $options['pjaxContainerId'] = $this->grid->pjaxSettings['options']['id'];
            }
            if ($this->filterType === GridView::FILTER_SELECT2 || $this->filterType === GridView::FILTER_TYPEAHEAD) {
                $options['data'] = $this->filter;
            }
            if ($this->filterType === GridView::FILTER_RADIO) {
                return Html::activeRadioList(
                    $this->grid->filterModel, $this->attribute, $this->filter, $this->filterInputOptions
                );
            }
        } else {
            if (empty($this->filterInputOptions['id'])) {
                $this->filterInputOptions['id'] = $this->attribute . '-filter_input';
            }
            if ($this->filterType === GridView::FILTER_CHECKBOX) {
                return Html::activeCheckbox($this->grid->filterModel, $this->attribute, $this->filterInputOptions);
            }
            $options = array_replace_recursive($this->filterWidgetOptions, $options);
            return '<label for="' . $this->filterInputOptions['id'] . '">' . Html::activeTextInput($model, $this->attribute, $this->filterInputOptions) . $error . '</label>';
        }

        if ($this->filterType === GridView::FILTER_CHECKBOX) {
            return Html::activeCheckbox($this->grid->filterModel, $this->attribute, $this->filterInputOptions);
        }
        $options = array_replace_recursive($this->filterWidgetOptions, $options);
        /* @var \kartik\base\Widget $widgetClass */
        return $widgetClass::widget($options);
    }

}
