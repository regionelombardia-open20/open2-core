<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\comments\views\comment\email
 * @category   CategoryName
 */

use open20\amos\core\helpers\Html;
use open20\amos\core\interfaces\BaseContentModelInterface;
use open20\amos\core\interfaces\ViewModelInterface;
use open20\amos\cwh\base\ModelContentInterface;
use open20\amos\core\module\BaseAmosModule;

/**
 * @var \open20\amos\core\record\Record $contextModel
 * @var \open20\amos\core\record\Record $model
 * @var \open20\amos\core\record\Record $model_reply
 */

if (!empty($user)) {
    $this->params['profile'] = $user->userProfile;
}

$link = '#comments_anchor';
$linkText = $contextModel->__toString();
$description = '-';

if (($contextModel instanceof BaseContentModelInterface) || $contextModel->hasMethod('getTitle')) {
    $linkText = $contextModel->getTitle();
}

if (method_exists($contextModel, 'getFullViewUrl')) {
    $link = \Yii::$app->urlManager->createAbsoluteUrl($contextModel->getFullViewUrl()) . "#comments_anchor";
}

if (($contextModel instanceof BaseContentModelInterface) || $contextModel->hasMethod('getDescription')) {
    $description = $contextModel->getDescription(true);
}

?>

<div style="border:1px solid #cccccc;padding:10px;margin-bottom: 10px;background-color: #ffffff;margin-top:20px">
    <div style="color:#000000;">
        <h2 style="font-size:2em;line-height: 1;margin:0;padding:10px 0;">
            <?= Html::a($linkText, $link, ['style' => 'color: green;']); ?>
        </h2>
    </div>

    <div style="box-sizing:border-box;font-size:13px;font-weight:normal;">
        
        Ciao <?= $user->userProfile->getNomeCognome() ?>,
        <br>

        <?php 
            if( !$model->isNewRecord ){
                echo $model->getCreatedUserProfile()->one()->getNomeCognome(); 
            }
        ?>

        <?= BaseAmosModule::t('amoscore', 'ti ha taggato in un commento.') ?>
           
        <br>
    </div>

    <div style="margin-top:20px">
          
        <?= BaseAmosModule::t('amoscore', 'Clicca qui per vederlo:') ?> <?= Html::a($linkText, $link, ['style' => 'color: green;']); ?>
    </div>

    <div style="box-sizing:border-box;padding-bottom: 5px;color:#000000;">
        <div style="margin-top:20px;">
            <div style="display: flex;width: 100%;">
                <div style="width: 50px; height: 50px; overflow: hidden;-webkit-border-radius: 50%; -moz-border-radius: 50%; border-radius: 50%;float: left;">
                    <?php
                        $layout = '{publisher}';
                        if ($model instanceof ModelContentInterface) {
                            $layout = '{publisher}{publishingRules}{targetAdv}';
                        }
                    ?>
                    <?php if ($model->getCreatedUserProfile() != null): ?>
                        <?php
                            if( !$model->isNewRecord ){
                                echo \open20\amos\admin\widgets\UserCardWidget::widget([
                                    'model' => $model->getCreatedUserProfile()->one(),
                                    'onlyAvatar' => true,
                                    'absoluteUrl' => true
                                ]);
                            }
                        ?>
                    <?php endif; ?>
                </div>

                <div style="margin-left: 20px;">
                    <?=
                        \open20\amos\core\forms\PublishedByWidget::widget([
                            'model' => $model,
                            'modelContext' => $contextModel,
                            'layout' => $layout,
                        ]) 
                    ?>

                    <!-- <span style="font-weight:normal;">< ?= $model->$model_field ?></span> -->
                </div>
            </div>
        </div>
    </div>

</div>
