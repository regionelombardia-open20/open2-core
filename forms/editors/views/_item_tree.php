<?php

use yii\helpers\Html;
use open20\amos\core\icons\AmosIcons;


/** @var  $model \frontend\modules\formezprojects\models\FormezNode */
$model = $b['model'];

?>
<div class="item-tree">
    <div class="d-flex">
        <div class="icon-collapse"><?php if (!empty($b['children'])) { ?><?= Html::a('<span class="icon-tree-collapse"></span>', "#$completeCollapseId", ['data-toggle' => 'collapse', 'aria-controls' => "$completeCollapseId", 'aria-expanded' => true]); ?><?php } ?></div>

        <div class="content-item-tree">
            <h5> <?= $b['title'] ?></h5>
        </div>

        <?php if (!empty($b['model'])) { ?>
            <div class="btn-actions">
                <?php echo \open20\amos\core\forms\ContextMenuWidget::widget([
                    'model' => $model,
                    'disableModify' => false,
                    'disableDelete' => false,
                ]) ?>
            </div>
        <?php } else if (!empty($b['link'])) { ?>
            <div class="btn-actions">
                <?= Html::a(\Yii::t('amoscore', "Visualizza"), $b['link'], [
                    'class' => 'btn btn-primary',
                    'title' => \Yii::t('amoscore', "Visualizza {title}", ['title' => $b['title']]),
                    'data-confirm' => \Yii::t('amoscore', "Sei sicuro di copiare quest nodo?")

                ]) ?>
            </div>
        <?php } ?>


    </div>
    <div class="description-node">
        <p><?= $b['description'] ?></p>
    </div>


</div>