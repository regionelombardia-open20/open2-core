<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms\views\widgets
 * @category   CategoryName
 */

/** @var \open20\amos\core\forms\PublishedContentsWidget $widget */

use open20\amos\core\icons\AmosIcons;
use open20\amos\core\views\AmosGridView;
use open20\amos\core\forms\AccordionWidget;

?>

<?= AccordionWidget::widget([
    'items' => [
        [
            'header' => $widget->modelIcon . $widget->modelLabel . ' <span class="bold">(' . $widget->data->totalCount . ')</span>',
            'content' => AmosGridView::widget([
                'dataProvider' =>  $widget->data,
                'summary' => '',
                'emptyText' => Yii::t('amoscore','Nessun elemento di questa categoria pubblicato dalla community'),
                'columns' => $widget->gridViewColumns
            ]),
        ]
    ],
    'headerOptions' => ['tag' => 'h2'],
    'clientOptions' => [
        'collapsible' => true,
        'active' => 'false',
        'icons' => [
            'header' => 'ui-icon-amos am am-plus-square',
            'activeHeader' => 'ui-icon-amos am am-minus-square',
        ],
    ],
    'options' => [
        'class' => ''
    ]
]); ?>
