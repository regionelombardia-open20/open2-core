<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\forms\views\widgets
 * @category   CategoryName
 */

/** @var \lispa\amos\core\forms\PublishedContentsWidget $widget */

use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\views\AmosGridView;

$this->registerCss("
    .chage-icon.btn{
        padding: 3px 12px;
    }
    span.text-to-change-open, span.text-to-change-close{
        padding-right: 5px;
    }
//    .badge-panel-heading{
//        position: relative; 
//        margin: -15px 0 0 20px;
//    }
");

$this->registerJs("
    $('.button-collapse').on('click', function(){
        var open = $(this).find('.text-to-change-open');
        var close = $(this).find('.text-to-change-close');
        if(open.hasClass('hidden')){
            close.removeClass('visible').addClass('hidden');
            open.removeClass('hidden').addClass('visible');
        }else{
            open.removeClass('visible').addClass('hidden');
            close.removeClass('hidden').addClass('visible'); 
        }
        var icon = $(this).find('.chage-icon');
        if(icon.hasClass('am-caret-down')){
            icon.removeClass('am-caret-down').addClass('am-caret-up');
        }else{
            icon.removeClass('am-caret-up').addClass('am-caret-down');
        }
    });
");

/**
 * @var string $contentType
 */
$contentType = str_replace(" ", "-", $widget->modelLabel);
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <span class="title"><?= $widget->modelLabel ?></span>
        <span class="badge badge-panel-heading"><?=$widget->data->totalCount?></span>
        <div class="button-collapse pull-right" data-toggle="collapse" data-target="#collapse-<?= $contentType ?>" >
            <span class="text-to-change-close visible"><?= Yii::t('amoscore', 'Chiudi') ?></span>
            <span class="text-to-change-open hidden"><?= Yii::t('amoscore', 'Mostra') ?></span>
            <?= AmosIcons::show('caret-up',['class' => 'chage-icon btn btn-secondary']) ?>
        </div>
    </div>
    <div id="collapse-<?= $contentType ?>" class="panel-body collapse in" aria-expanded="true">
        <div class="container-items">
            <?= AmosGridView::widget([
                'dataProvider' =>  $widget->data,
                'summary' => '',
                'emptyText' => Yii::t('amoscore','Nessun elemento di questa categoria pubblicato dalla community'),
                'columns' => $widget->gridViewColumns
            ]); ?>
        </div>
    </div>
</div>
