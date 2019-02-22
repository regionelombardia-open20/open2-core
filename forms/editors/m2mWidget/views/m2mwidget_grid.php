<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\editors\m2mwidget\views
 * @category   CategoryName
 */

use lispa\amos\core\forms\editors\assets\EditorsAsset;
use lispa\amos\core\forms\editors\m2mWidget\M2MWidget;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\module\BaseAmosModule;
use lispa\amos\core\utilities\JsUtility;
use lispa\amos\core\views\DataProviderView;
use yii\bootstrap\Modal;

/**
 * @var \yii\web\View $this
 * @var \yii\db\ActiveRecord $searchModel
 * @var bool $viewSearch
 * @var bool $useCheckbox
 */

EditorsAsset::register($this);

$list = [
    'name' => 'list',
    'label' => BaseAmosModule::t('amoscore', '{iconaLista}' . Html::tag('p', BaseAmosModule::t('amoscore', 'Card')), [
        'iconaLista' => AmosIcons::show('view-list')
    ]),
    'url' => '?currentView=list'
];
$grid = [
    'name' => 'grid',
    'label' => BaseAmosModule::t('amoscore', '{iconaTabella}' . Html::tag('p', BaseAmosModule::t('amoscore', 'Tabella')), [
        'iconaTabella' => AmosIcons::show('view-list-alt')
    ]),
    'url' => '?currentView=grid'
];

$GridId = isset($firstGridId) ? $firstGridId . '-association' : 'grid-view-sedi';
//$gridViewContainerId = isset($gridViewContainerId) ? $gridViewContainerId : M2MWidget::defaultGridViewContainerId();
$gridViewContainerId =  isset($gridViewContainerId) ? $gridViewContainerId : 'm2mwidget-grid-view-container';
if (isset($isModal) && $isModal) {
    $modalId = isset($firstGridId) ? $firstGridId . '-modal' : $GridId . '-modal';
}
$pjaxContainerId = $GridId . '-pjax';
$currentView = $grid;
if (!is_null($listView)) {
    $currentView = $list;
    $listViewArray = is_array($listView) ? $listView : ['itemView' => $listView];
} else {
    $listViewArray = [];
}
\yii\widgets\PjaxAsset::register($this);

?>
<?php
if (isset($modalId)) {
    Modal::begin([
        'id' => $modalId,
        'size' => Modal::SIZE_LARGE
    ]);

    $js = JsUtility::getM2mModalSave(isset($firstGridId) ? $firstGridId : 'm2m-grid', $this->params['postName'], $this->params['postKey']);
} else {
    $js = JsUtility::getM2mSecondGridPagination($GridId, $this->params['postName'], $this->params['postKey'], $useCheckbox);
}
$this->registerJs($js);
?>

<?php if (isset($viewSearch) && ($viewSearch === true)): ?>
    <?= $this->render('_search', [
        'model' => $searchModel,
        'pjaxContainerId' => $pjaxContainerId,
        'gridViewContainerId' => $gridViewContainerId,
        'gridId' => $GridId,
        'isModal' => isset($isModal) ? $isModal : false,
        'useCheckbox' => $useCheckbox
    ]); ?>
<?php endif; ?>

<div id="<?= $gridViewContainerId ?>">
    <div id="<?= $GridId ?>" data-pjax-container="<?= $pjaxContainerId ?>" data-pjax-timeout="1000">
        <?= DataProviderView::widget([
            'dataProvider' => $this->params['modelTargetData'],
            'currentView' => $currentView,
            'gridView' => [
                'columns' => $this->params['columnsArray'],
                'rowOptions' => function ($model, $key, $index, $grid) {
                    $class = '';
                    return ['class' => $class];
                },
            ],
            'listView' => $listViewArray
        ]) ?>
    </div>
</div>
