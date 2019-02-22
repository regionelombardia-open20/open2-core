<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views\layouts\parts
 * @category   CategoryName
 */

use yii\helpers\Html;

/** @var bool|false $disablePlatformLinks  - if true all the links to dashboard, settings, etc are disabled */
$disablePlatformLinks = isset($this->params['disablePlatformLinks']) ? $this->params['disablePlatformLinks'] : false;

$logo = isset(Yii::$app->params['logo'])?
    Html::img( Yii::$app->params['logo'], [
        'class' => 'img-responsive logo-amos',
        'alt' => 'logo '. Yii::$app->name
    ])
    : '<p>'.Yii::$app->name.'</p>';
$logoUrl = $disablePlatformLinks ? null : Yii::$app->homeUrl;
$logoOptions = [];
$title = isset(Yii::$app->params['logo'])?  \lispa\amos\core\module\BaseAmosModule::t('amoscore', 'vai alla home page') : Yii::$app->name;
$logoOptions['title'] = $title;
if(!isset(Yii::$app->params['logo'])){
    $logoOptions['class'] = 'title-text';
}
?>

<div class="container">

    <?= Html::a($logo, $logoUrl, $logoOptions); ?>

    <?php if (isset(Yii::$app->params['logo-text']) ): ?>
        
        <p class="title-text">  <?= Yii::$app->params['logo-text'] ?></p>
        
    <?php endif; ?>

    <?php if (isset(Yii::$app->params['logo-signature'])): ?>
        <?php
        $signature = Html::img(Yii::$app->params['logo-signature'], [
            'class' => 'img-responsive signature pull-right',
            'alt' => \lispa\amos\core\module\BaseAmosModule::t('amoscore', 'logo firma')
        ]);
        ?>
        <?php if($disablePlatformLinks): ?>
            <?= $signature ?>
        <?php else: ?>
            <?=
            Html::a( $signature, [Yii::$app->homeUrl,],  ['title' => \lispa\amos\core\module\BaseAmosModule::t('amoscore', 'vai alla home page')]);
            ?>
        <?php endif;?>
    <?php endif; ?>
</div>