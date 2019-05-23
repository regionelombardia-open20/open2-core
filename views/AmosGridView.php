<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views
 * @category   CategoryName
 */

namespace lispa\amos\core\views;

use Closure;
use lispa\amos\core\module\BaseAmosModule;
use Yii;
use yii\grid\Column;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\LinkPager;

/**
 * Class AmosGridView
 * @package lispa\amos\core\views
 */
class AmosGridView extends GridView
{
    private $class_thead = '';
    public $title;
    public $name = 'grid';
    
    /* INIZIO ATTRIBUTI per usare una colonna di tipo  'class' => 'kartik\grid\DataColumn' */
    public $bootstrap;
    public $showPageSummary = true;
    public $showPager = true;

    public $autoXlFormat;
    public $pjax;
    /* FINE ATTRIBUTI */
    
    public $panelTemplate = <<< HTML
            {summary}
            {items}
            {pager}
        
HTML;
    
    public $layout = "<div class=\"table_switch table-responsive\"> {items} \n {summary} <br> {pager} </div>";
    public $summary = "Risultati visualizzati {count} - Risultati da {begin} a {end} su un totale di {totalCount} - Pagina {page} di {pageCount}";
    public $tableOptions = ['class' => 'table table-hover'];
    
    /**
     * @var string the default data column class if the class name is not explicitly specified when configuring a data column.
     * Defaults to 'lispa\amos\core\views\grid\DataColumn'.
     */
    public $dataColumnClass = 'lispa\amos\core\views\grid\DataColumn';
    
    /**
     * @var bool $enableExport Enable export of the data present in the grid view without other columns.
     */
    public $enableExport = false;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if($this->showPageSummary) {
            $this->summary = Yii::t('amoscore', 'Risultati visualizzati {count} - Risultati da {begin} a {end} su un totale di {totalCount} - Pagina {page} di {pageCount}');
        }
        else {
            $this->summary = '';
        }

        if ($this->enableExport) {
            $queryParams = Yii::$app->request->getQueryParams();
            $queryParams['download'] = true;
            Yii::$app->request->setQueryParams($queryParams);
        }
    }
    
    /* public function getLabel($column){
      return html_entity_decode(strip_tags($column->renderHeaderCell()));
      } */
    
    /**
     * Sovrascriviamo la funzione nativa per assegnare al tag TD l'attributo 'title' con valore prelevato dalla label dell'HeaderTable
     * Renders the filter.
     * @return string the rendering result.
     */
    public function renderFilters()
    {
        if ($this->filterModel !== null) {
            $cells = [];
            foreach ($this->columns as $column) {
                /* @var $column Column */
                $column->filterOptions['title'] = html_entity_decode(strip_tags($column->renderHeaderCell()));
                $cells[] = $column->renderFilterCell();
            }
            return Html::tag('tr', implode('', $cells), $this->filterRowOptions);
        } else {
            return '';
        }
    }
    
    /**
     * Sovrascriviamo la funzione nativa per assegnare al tag TR la classe filters
     * e a TD la classe input_element
     * stilizzata in modo tale da essere vista nel mobile a differenza del comportamento base
     * della tabella responsiva
     * Renders the table header.
     * @return string the rendering result.
     */
    public function renderTableHeader()
    {
        $cells = [];
        foreach ($this->columns as $column) {
            /* @var $column Column */
            $column->headerOptions['title'] = html_entity_decode(strip_tags($column->renderHeaderCell()));
            
            //if there is a input add class filters to TR and input_element to TD
            if (strpos(strtolower($column->renderHeaderCell()), "<input") !== false) {
                $this->headerRowOptions['class'] = 'filters';
                $column->headerOptions['class'] = 'input_element';
            }
            $cells[] = $column->renderHeaderCell();
        }
        $content = Html::tag('tr', implode('', $cells), $this->headerRowOptions);
        if ($this->filterPosition == self::FILTER_POS_HEADER) {
            $content = $this->renderFilters() . $content;
        } elseif ($this->filterPosition == self::FILTER_POS_BODY) {
            $content .= $this->renderFilters();
            $this->class_thead = 'thead_static';
        }
        return "<thead class='" . $this->class_thead . "'>\n" . $content . "\n</thead>";
    }
    
    /**
     * Sovrascriviamo la funzione nativa per assegnare al tag TD l'attributo 'title' con valore prelevato dalla label dell'HeaderTable
     * Renders a table row with the given data model and key.
     * @param mixed $model the data model to be rendered
     * @param mixed $key the key associated with the data model
     * @param integer $index the zero-based index of the data model among the model array returned by [[dataProvider]].
     * @return string the rendering result
     */
    public function renderTableRow($model, $key, $index)
    {
        $cells = [];
        /* @var $column Column */
        foreach ($this->columns as $column) {
            //remove html tag and decode html entity    		
            $column->contentOptions['title'] = html_entity_decode(strip_tags($column->renderHeaderCell()));
            $cells[] = $column->renderDataCell($model, $key, $index);
        }
        if ($this->rowOptions instanceof Closure) {
            $options = call_user_func($this->rowOptions, $model, $key, $index, $this);
        } else {
            $options = $this->rowOptions;
        }
        
        $options['data-key'] = is_array($key) ? json_encode($key) : (string)$key;
        
        return Html::tag('tr', implode('', $cells), $options);
    }
    
    /**
     * Renders the table body.
     * @return string the rendering result.
     */
    public function renderTableBody()
    {
        $models = array_values($this->dataProvider->getModels());
        $keys = $this->dataProvider->getKeys();
        $rows = [];
        foreach ($models as $index => $model) {
            $key = $keys[$index];
            if ($this->beforeRow !== null) {
                $row = call_user_func($this->beforeRow, $model, $key, $index, $this);
                if (!empty($row)) {
                    $rows[] = $row;
                }
            }
            
            $rows[] = $this->renderTableRow($model, $key, $index);
            
            if ($this->afterRow !== null) {
                $row = call_user_func($this->afterRow, $model, $key, $index, $this);
                if (!empty($row)) {
                    $rows[] = $row;
                }
            }
        }
        
        if (empty($rows)) {
            /*$emptyRow = "<tbody>\n<tr>";
            $i = 0;
            foreach ($this->columns as $column) {
                if ($i == 0) {
                    $options = [
                        'style' => ''
                    ];
                    if (isset($column->contentOptions['headers'])) {
                        $options['headers'] = $column->contentOptions['headers'];
                    }
                    $emptyRow .= Html::tag('td', $this->renderEmpty(), $options);
                } else {
                    $options = [
                        'class' => 'hidden'
                    ];
                    if (isset($column->contentOptions['headers'])) {
                        $options['headers'] = $column->contentOptions['headers'];
                    }
                    $emptyRow .= Html::tag('td', '', $options);
                }
                $i++;
            }
            $emptyRow .= "</tr>\n</tbody>";*/

            $emptyRow = "<tbody></tbody>";
            $emptyMessage = ((isset($this->emptyText)) && is_string($this->emptyText)) ? $this->emptyText : BaseAmosModule::t('amoscore','Non sono presenti contenuti');
            $emptyRow .= Html::tag('p',BaseAmosModule::t('amoscore',$emptyMessage),['class' => 'grid-view-empty']);
            
            return $emptyRow;
        } else {
            return "<tbody>\n" . implode("\n", $rows) . "\n</tbody>";
        }
    }
    
    /**
     * inherit
     */
    /**
     * Renders the pager.
     * @return string the rendering result
     */
    public function renderPager()
    {
        $pagination = $this->dataProvider->getPagination();
        if ($this->showPager == false || $pagination === false || $this->dataProvider->getCount() <= 0) {
            return '';
        }
        /* @var $class LinkPager */
        $pager = $this->pager;
        $class = ArrayHelper::remove($pager, 'class', AmosLinkPager::className());
        $pager['pagination'] = $pagination;
        $pager['view'] = $this->getView();
        return $class::widget($pager);
    }
}
