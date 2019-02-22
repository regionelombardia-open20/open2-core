<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\forms\views\widgets
 * @category   CategoryName
 */

//use lispa\amos\core\forms\InteractionMenuWidget;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\module\BaseAmosModule;

/**
 * @var string $contentCreatorAvatar Avatar of the content creator.
 * @var string $contentCreatorNameSurname Name and surname of the content creator.
 * @var bool $hideInteractionMenu If true set the class to hide the interaction menu.
 * @var array $interactionMenuButtons Sets the interaction menu buttons.
 * @var array $interactionMenuButtonsHide Sets the interaction menu buttons to hide.
 * @var string $publicatonDate Publication date of the content.
 * @var string $customContent Custom content.
 * @var \lispa\amos\core\forms\ItemAndCardHeaderWidget $widget
 */

?>

<div class="post-header col-xs-12 nop">
    <div class="post-header-avatar pull-left">
        <?= $contentCreatorAvatar ?>
    </div>
    <p class="creator"><?= Html::a($contentCreatorNameSurname, $widget->getContentCreator()->getFullViewUrl(), [
            'title' => $widget->getContentCreatorLinkTitle()
        ]) ?></p>
    <?php if (isset($contentPrevalentPartnership) && $contentPrevalentPartnership) : ?>
        <p class="card-prevalent-partnership"><i>(<?= $contentPrevalentPartnership ?>)</i></p>
    <?php endif; ?>
    <?php if (isset($contentCreatorTargets) && $contentCreatorTargets) : ?>
        <p class="card-creator-targets"><strong><?= $contentCreatorTargets ?></strong></p>
    <?php endif; ?>
    <?php if (isset($customContent) && $customContent) : ?>
        <div class="custom-content"><?= $customContent; ?></div>
    <?php endif; ?>
    <?php if ($publicatonDate): ?>
        <p class="publication-date"><?= BaseAmosModule::t('amoscore', 'Pubblicato il') ?> <?= $publicatonDate ?></p>
    <?php endif; ?>
    <?php
    //    echo InteractionMenuWidget::widget([
    //        'hideInteractionMenu' => $hideInteractionMenu,
    //        'interactionMenuButtons' => $interactionMenuButtons,
    //        'interactionMenuButtonsHide' => $interactionMenuButtonsHide,
    //        'model' => $model
    //    ]);
    ?>
</div>
