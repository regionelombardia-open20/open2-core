<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\forms\views\widgets
 * @category   CategoryName
 */

use lispa\amos\core\forms\AmosCarouselWidget;
use lispa\amos\core\forms\ItemAndCardHeaderWidget;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\module\BaseAmosModule;
use lispa\amos\core\views\toolbars\StatsToolbar;

/**
 * @var \lispa\amos\core\record\Record $model
 */

$contentTitle = AmosCarouselWidget::getContentTitle($model);
$contentShortDescription = AmosCarouselWidget::getContentShortDescription($model);
$contentLinkTitle = AmosCarouselWidget::getContentReadAllLinkTitle($model) . " '" . $contentTitle . "'";
$contentDateArray = AmosCarouselWidget::getContentDateArray($model);
$contentImage = Html::img(AmosCarouselWidget::getContentImageUrl($model), [
    'class' => 'img-responsive',
    'alt' => AmosCarouselWidget::getContentImageAlt($model) . " '" . $contentTitle . "'"
]);
$contentViewUrl = AmosCarouselWidget::getContentViewUrl($model);

?>

<!-- BEGIN LEFT PART -->
<div class="col-md-7 col-xs-12 nop">

    <!-- Content image -->
    <div class="evidence-image">
        <?= Html::a($contentImage, $contentViewUrl, ['title' => $contentLinkTitle]) ?>
    </div>

</div>
<!-- END LEFT PART -->

<!-- BEGIN RIGHT PART -->
<div class="col-md-5 col-xs-12">

    <!-- Content header -->
    <div class="evidence-header col-xs-12 nop">
        <div class="col-xs-3 nop">
            <?php if (!empty($contentDateArray)): ?>
                <div class="evidence-data">
                    <span><?= BaseAmosModule::t('amoscore', '#amos_carousel_widget_published'); ?></span>
                    <span><?= $contentDateArray['day']; ?></span>
                    <span><?= $contentDateArray['month']; ?></span>
                    <span><?= $contentDateArray['year']; ?></span>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-xs-9 nop">
            <?= ItemAndCardHeaderWidget::widget([
                'model' => $model,
                'publicationDateNotPresent' => true,
                'showPrevalentPartnershipAndTargets' => true,
            ]); ?>
        </div>
    </div>

    <!-- Content body -->
    <div class="evidence-body col-xs-12 nop">
        <a href="<?= $contentViewUrl; ?>" class="title" title="<?= $contentLinkTitle; ?>">
            <h3><?= $contentTitle ?></h3>
        </a>
        <p class="text"><?= $contentShortDescription; ?></p>
        <a href="<?= $contentViewUrl; ?>" class="read-all" title="<?= $contentLinkTitle; ?>">
            <?= BaseAmosModule::t('amoscore', '#amos_carousel_widget_read_all'); ?>
        </a>
    </div>

    <!-- Content footer -->
    <div class="evidence-footer col-xs-12 nop"> <!-- platform speed -->
        <?php /* StatsToolbar::widget([
            'model' => $model,
            'layoutType' => StatsToolbar::LAYOUT_HORIZONTAL,
            'disableLink' => true
        ]); */ ?>
    </div>

</div>
<!-- END RIGHT PART -->
