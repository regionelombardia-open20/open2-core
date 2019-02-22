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

use yii\widgets\LinkPager;
use lispa\amos\core\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * Class AmosLinkPager
 * Renders
 *
 * @package lispa\amos\core\views
 */
class AmosLinkPager extends LinkPager {

    public $showSummary = false;
    public $bottomPositionSummary = false;
    public $classSummary = 'text-center';
    private $results_from;
    private $results_to;
    private $result_displayed;
    private $page_number;
    private $total_pages;

    /**
     * Executes the widget.
     * This overrides the parent implementation by displaying the generated page buttons.
     */
    public function run() {
        if ($this->registerLinkTags) {
            $this->registerLinkTags();
        }
        if ($this->showSummary) {
            $this->setSummary();
            $render = "";
            if ($this->bottomPositionSummary) {
                $render .= $this->renderPageButtons();
            }
            if ($this->pagination->totalCount > $this->pagination->pageSize) {
                $render .= "<div class=\"{$this->classSummary}\">Risultati visualizzati {$this->result_displayed} - Risultati da {$this->results_from} a {$this->results_to} su un totale di {$this->pagination->totalCount} - Pagina {$this->page_number} di {$this->total_pages}</div>";
            }
            if (!$this->bottomPositionSummary) {
                $render .= $this->renderPageButtons();
            }
            echo $render;
        } else {
            echo $this->renderPageButtons();
        }
    }

    protected function setSummary() {

        if (NULL !== ($this->pagination->pageSize) && NULL !== ($this->pagination->page)) {
            $this->page_number = $this->pagination->page + 1;
            $this->total_pages = ceil($this->pagination->totalCount / $this->pagination->pageSize);
            if ($this->pagination->page > 0) {
                if ($this->pagination->totalCount >= ($this->pagination->pageSize * ($this->page_number))) {
                    $this->result_displayed = $this->pagination->pageSize;
                    $this->results_from = ($this->pagination->pageSize * $this->pagination->page) + 1;
                    $this->results_to = ($this->pagination->pageSize * $this->pagination->page) + $this->pagination->pageSize;
                } else {
                    $this->result_displayed = $this->pagination->totalCount - ($this->pagination->pageSize * $this->pagination->page);
                    $this->results_from = ($this->pagination->pageSize * $this->pagination->page) + 1;
                    $this->results_to = $this->pagination->totalCount;
                }
            } else {
                if ($this->pagination->totalCount >= $this->pagination->pageSize) {
                    $this->result_displayed = $this->pagination->pageSize;
                    $this->results_from = 1;
                    $this->results_to = $this->pagination->pageSize;
                } else {
                    $this->result_displayed = $this->pagination->totalCount;
                    $this->results_from = 1;
                    $this->results_to = $this->pagination->totalCount;
                }
            }
        }
    }

    /**
     * Renders a page button.
     * You may override this method to customize the generation of page buttons.
     * @param string $label the text label for the button
     * @param int $page the page number
     * @param string $class the CSS class for the page button.
     * @param bool $disabled whether this page button is disabled
     * @param bool $active whether this page button is active
     * @return string the rendering result
     */
    protected function renderPageButton($label, $page, $class, $disabled, $active)
    {
        $options = ['class' => empty($class) ? $this->pageCssClass : $class];
        if ($active) {
            Html::addCssClass($options, $this->activePageCssClass);
        }
        if ($disabled) {
            Html::addCssClass($options, $this->disabledPageCssClass);
            $tag = ArrayHelper::remove($this->disabledListItemSubTagOptions, 'tag', 'span');

            return Html::tag('li', Html::tag($tag, $label, $this->disabledListItemSubTagOptions), $options);
        }
        $linkOptions = $this->linkOptions;
        $linkOptions['data-page'] = $page;
        if(!isset($linkOptions['title'])){
            if($label == '&raquo;'){
                $linkOptions['title'] = \Yii::t('amoscore', 'Next page');
            } else if($label == '&laquo;'){
                $linkOptions['title'] = \Yii::t('amoscore', 'Previous page');
            } else {
                $linkOptions['title'] = \Yii::t('amoscore', 'Page') . ' ' . $label;
            }
        }

        return Html::tag('li', Html::a($label, $this->pagination->createUrl($page), $linkOptions), $options);
    }

}
