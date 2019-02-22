<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views\layouts\parts
 * @category   CategoryName
 */

use lispa\amos\core\helpers\Html;
use lispa\amos\core\icons\AmosIcons;

/**
 * @var yii\web\View $this
 * @var \lispa\amos\core\record\Record $model
 * @var \lispa\amos\core\components\PartQuestionarioAbstract $partsQuestionario
 * @var bool $hidePartsLabel
 * @var bool $hidePartsUrl
 */

$partsArray = $partsQuestionario->getParts();
$stepsCount = count($partsArray);

?>

<div class="progress-container progress-container-lg col-xs-12 nop">
    <ul class="progress-indicator">
        <?php foreach ($partsArray as $part): ?>
            <?php
            $partContent = Html::tag('span', '', ['class' => 'bubble-indicator' . (($stepsCount < 8) ? ' long-line' : '')]);
            $partContent .= Html::tag('span', $part['index'], ['class' => 'key-indicator']);
            $partContent .= $part['label'];
            ?>
            <li class="<?= $part['status'] ?>" title="<?= $part['title'] ?>">
                <?php if (!$hidePartsUrl && $part['url']): ?>
                    <?= Html::a($partContent, $part['url'], ['title' => $part['label']]) ?>
                <?php else: ?>
                    <?= $partContent ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2 class="part<?= ((isset($hidePartsLabel) && $hidePartsLabel) ? ' hidden' : '') ?>"><?= $partsQuestionario->active['index'] ?>. <?= $partsQuestionario->active['label'] ?></h2>

    <ul class="progress-indicator subpart">
        <?php foreach ($partsQuestionario->getSubParts() as $subpart): ?>
            <?php
            $subpartContent = Html::tag('span', '', ['class' => 'bubble-indicator']);
            $subpartContent .= Html::beginTag('span', ['class' => 'txt']);
            $subpartContent .= Html::beginTag('span', ['class' => 'key-indicator']);
            $subpartContent .= '  ' . $partsQuestionario->active['index'] . '.' . $subpart['index'];
            $subpartContent .= Html::endTag('span');
            $subpartContent .= ' ' . $subpart['label'];
            $subpartContent .= Html::endTag('span');
            ?>
            <li class="<?= $subpart['status'] ?>">
                <?php if (!$hidePartsUrl && $subpart['url']): ?>
                    <?= Html::a($subpartContent, $subpart['url'], ['title' => $subpart['label']]) ?>
                <?php else : ?>
                    <?= $subpartContent ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    
    <?php if ($partsQuestionario->activeChild['index']): ?>
        <h3 class="subpart"><?= $partsQuestionario->active['index'] ?>
            .<?= $partsQuestionario->activeChild['index'] ?> <?= ($partsQuestionario->activeChild) ? $partsQuestionario->activeChild['label'] : '' ?></h3>
    <?php endif; ?>
    
    <?php if ($partsQuestionario->active['description']): ?>
        <h3><?= $partsQuestionario->active['description'] ?> </h3>
    <?php endif; ?>
</div>


<?php
$partsKeys = array_keys($partsArray);
$lastPartsIndex = $stepsCount - 1;
?>

<!-- MENU MOBILE VERSION  (500px) -->
<?php foreach ($partsKeys as $key => $partsKey): ?>
    <?php
    // Elemento precedente
    $prevKeyIndex = $key - 1;
    if ($prevKeyIndex < 0) {
        $prevKeyIndex = $lastPartsIndex;
    }
    $prevElement = $partsArray[$partsKeys[$prevKeyIndex]];
    
    // Elemento successivo
    $nextKeyIndex = $key + 1;
    if ($nextKeyIndex > $lastPartsIndex) {
        $nextKeyIndex = 0;
    }
    $nextElement = $partsArray[$partsKeys[$nextKeyIndex]];
    ?>
    <?php if ($partsQuestionario->active['status'] == $partsArray[$partsKey]['status']): ?>
        <div class="progress-container progress-container-sm  col-xs-12">
            <?php if ($prevKeyIndex != $lastPartsIndex): ?>
                <a href="<?= $prevElement['url'] ?>" title="<?= $prevElement['label'] ?>">
                    <?= AmosIcons::show('chevron-left', ['class' => 'pull-left am-2']); ?>
                </a>
            <?php endif; ?>
            <p>
                <span class="key-indicator"><?= ($partsArray[$partsKey]['index']) ?></span>
                <?= $partsArray[$partsKey]['label'] ?>
            </p>
            <?php if ($nextKeyIndex != 0): ?>
                <a href="<?= $nextElement['url'] ?>" title="<?= $nextElement['label'] ?>">
                    <span><?= AmosIcons::show('chevron-right', ['class' => 'pull-right am-2']); ?></span>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
