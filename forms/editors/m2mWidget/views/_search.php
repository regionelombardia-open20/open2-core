<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms\editors\m2mwidget\views
 * @category   CategoryName
 */

use open20\amos\core\helpers\Html;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\utilities\JsUtility;
use yii\web\View;

/**
 * @var yii\web\View $this
 * @var \yii\db\ActiveRecord $model
 * @var string $pjaxContainerId
 * @var string $gridViewContainerId
 * @var string $gridId
 * @var bool $useCheckbox
 */

$post = Yii::$app->getRequest()->post();
$genericSearchFieldId = 'm2mwidget-generic-search-textinput';
$fromGenericSearchFieldId = 'm2mwidget-from-generic-search-hiddeninput';
$resetId = 'm2mwidget-generic-search-reset-btn';
$submitId = 'm2mwidget-generic-search-submit-btn';

$js = JsUtility::getM2mSecondGridSearch($gridId, $this->params['postName'], $this->params['postKey'], $isModal, $useCheckbox);
$this->registerJs($js, View::POS_READY);

?>
<div class="m2mwidget-generic-search">
    <div class="col-xs-12 nop m-15-0">
        <div class="col-sm-6 col-lg-4 nop">
            <!-- TODO Rimuovere hiddenInput fromGenericSearch quando funzionerà il pjax -->
            <?= Html::hiddenInput('fromGenericSearch', 0, [
                'id' => $fromGenericSearchFieldId
            ]); ?>

            <?= Html::textInput('genericSearch', (isset($post['genericSearch']) ? $post['genericSearch'] : null), [
                'placeholder' => BaseAmosModule::t('amoscore', 'Search') . '...',
                'id' => $gridId . '-search-field', 'class' => 'form-control'
            ]); ?>
        </div>

        <div class="col-sm-6 col-lg-8">
            <?= Html::button(BaseAmosModule::t('amoscore', 'Reset'), ['class' => 'btn btn-secondary', 'id' => $gridId . '-reset-search-btn']) ?>
            <?= Html::button(BaseAmosModule::t('amoscore', 'Search'), ['class' => 'btn btn-navigation-primary', 'id' => $gridId . '-search-btn']) ?>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
