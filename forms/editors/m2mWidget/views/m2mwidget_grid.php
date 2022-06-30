<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\editors\m2mwidget\views
 * @category   CategoryName
 */
use open20\amos\core\forms\editors\assets\EditorsAsset;
use open20\amos\core\helpers\Html;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\utilities\JsUtility;
use open20\amos\core\views\DataProviderView;
use yii\bootstrap\Modal;

/**
 * @var \yii\web\View $this
 * @var \yii\db\ActiveRecord $searchModel
 * @var \open20\amos\core\forms\editors\m2mWidget\M2MWidget $widget
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

$icon = [
    'name' => 'icon',
    'label' => BaseAmosModule::t('amoscore', '{iconaElenco}' . Html::tag('p', BaseAmosModule::t('amoscore', 'Icone')), [
        'iconaElenco' => AmosIcons::show('grid')
    ]),
    'url' => '?currentView=icon'
];

$GridId = isset($firstGridId) ? $firstGridId . '-association' : 'grid-view-sedi';
//$gridViewContainerId = isset($gridViewContainerId) ? $gridViewContainerId : M2MWidget::defaultGridViewContainerId();
$gridViewContainerId = isset($gridViewContainerId) ? $gridViewContainerId : 'm2mwidget-grid-view-container';
if (isset($isModal) && $isModal) {
    $modalId = isset($firstGridId) ? $firstGridId . '-modal' : $GridId . '-modal';
}
$pjaxContainerId = $GridId . '-pjax';
$currentView = $grid;
$listViewArray = [];
if (!is_null($listView)) {
    $currentView = $list;
    $listViewArray = is_array($listView) ? $listView : ['itemView' => $listView];
}
if (!is_null($iconView)) {
    $currentView = $icon;
    $listViewArray = is_array($iconView) ? $iconView : ['itemView' => $iconView];
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
    <?=
    $this->render('_search', [
        'model' => $searchModel,
        'widget' => $widget,
        'pjaxContainerId' => $pjaxContainerId,
        'gridViewContainerId' => $gridViewContainerId,
        'gridId' => $GridId,
        'isModal' => isset($isModal) ? $isModal : false,
        'useCheckbox' => $useCheckbox
    ]);
    ?>
<?php endif; ?>

<div id="<?= $gridViewContainerId ?>">
    <?php
    // check if parameter disable pagination is set true
    if (Yii::$app->getRequest()->get('disablePagination') == true) {
        $this->params['modelTargetData']->pagination = false;
    }
    ?>

        <?=
        DataProviderView::widget([
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
        ])
        ?>
</div>
