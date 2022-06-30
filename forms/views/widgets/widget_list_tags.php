<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms\views\widgets
 * @category   CategoryName
 */

use open20\amos\core\helpers\Html;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\views\AmosGridView;
use yii\bootstrap\Modal;
use yii\data\ActiveDataProvider;
use yii\widgets\Pjax;

/**
 * @var \yii\web\View $this
 * @var \open20\amos\tag\models\Tag $root
 * @var \yii\base\View $this
 * @var array $allRootTags
 * @var array $allTags
 */

if ($viewFilesCounter) {
    $this->registerJs(
        <<<JS
    
    var filesQuantity = "$filesQuantity";
    
    var section_title = $("#section-tags").find("h2");

    section_title.append(" (" + filesQuantity + ")");
    
    if(filesQuantity == 0){
        section_title.addClass("section-disabled");
    }
    
JS
    );
}

$dataProvider->pagination->setPageSize($pageSize);
$searchModule = Yii::$app->getModule('search');
$currentModule = Yii::$app->controller->module->getUniqueId();

?>
<div class="m-t-25"><strong class="text-uppercase"><?= BaseAmosModule::t('amoscore', 'Tag di interesse'); ?></strong></div>
<?php if ($filesQuantity == 0) { ?>
    <div class="no-items text-muted"><?= BaseAmosModule::t('amoscore', 'Non sono presenti aree di interesse associate a questo contenuto'); ?></div>
    <div class="tags-list-all">
    </div>
<?php } else { ?>

    <div class="tags-list-all m-t-30">


        
        <?php foreach ($dataProvider->models as $tag) { ?>
            <div class="tags-bi" data-tag="<?= $tag->id ?>" data-toggle="tooltip" title="<?= $tag->tagRoot->nome . ($tag->path ? " / " . $tag->path : "") ?>">
                <div class="d-flex align-items-center">
                    <div>
                        <span class="mdi mdi-tag-multiple"></span>
                    </div>
                    <div>
                        <span class="tag-label">
                            <?= $tag->nome ?>
                        </span>
                    </div>

                </div>

            </div>
        <?php } ?>
        <?php if ($filesQuantity > $pageSize) {
            echo Html::tag(
                'small',
                Html::a(BaseAmosModule::t('amoscore', '#view_all'), 'javascript:void(0);', [
                    'data-toggle' => 'modal',
                    'data-target' => '#view-all-tags',
                ]),
                ['class' => 'm-l-20']
            );
        } ?>
    </div>


    <?php
    // -------------- MODAL VIEW ALL TAGS --------------------
    Modal::begin([
        'id' => 'view-all-tags',
        'header' => "<h3>" . BaseAmosModule::t('amoscore', 'Tag') . "</h3>",
    ]);
    Pjax::begin([
        'id' => 'pjax-container-view-all',
        'timeout' => 2000,
        'enablePushState' => false,
        'enableReplaceState' => false,
        'clientOptions' => ['data-pjax-container' => 'grid-view-all-tags', 'method' => 'POST']
    ]); ?>
    <div class="tags-list-all m-t-20 m-b-20">
        <?php foreach ($dataProvider->models as $tag) { ?>
            <div class="tags-bi" data-tag="<?= $tag->id ?>" data-toggle="tooltip" title="<?= $tag->tagRoot->nome . ($tag->path ? " / " . $tag->path : "") ?>">
                <div class="d-flex align-items-center">
                    <div>
                        <span class="mdi mdi-tag-multiple"></span>
                    </div>
                    <div>
                        <span class="tag-label">
                            <?= $tag->nome ?>
                        </span>
                    </div>

                </div>

            </div>
        <?php } ?>
    </div>
<?php Pjax::end();

    Modal::end();
} ?>