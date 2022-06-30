<?php

use open20\amos\core\helpers\Html;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\module\BaseAmosModule;

use yii\bootstrap\Modal;

$module = \Yii::$app->controller->module->id;
$controller = \Yii::$app->controller->id;

$strLike = \Yii::t('amosapp','Metti mi piace');
$strNotLike = \Yii::t('amosapp','Togli mi piace');
$labelLike = (($lme == 'notlikeme') ? $strLike:  $strNotLike) ;


$js = <<<JS
$(document).ready(function () {
  var uid;
  var cid;
  var mid;
  var act;
  
  $('#like, #like-to').on('click', function(e) {
    e.preventDefault();
  
    uid = $('#like-uid').data('key');
    cid = $('#like-cid').data('key');
    mid = $('#like-mid').data('key');
    action = '/$module/$controller/'+this.id;
    act = this.id;

        $.ajax({
          url: action,
          async: true,
          type: 'get',
          dataType: 'json',
          data: {
            uid: uid,
            cid: cid,
            mid: mid
          },
          success: function(data) {
            if  (data) {
              if (act == 'like-to') {
                $("#openmodal-preview").html(data);
                $("#openModelUsers").modal('show'); 
              }
      
              if (act == 'like') {
                $('#n-piacea').html(data.tot);
                $('#like').attr('class', data.class);
                if(data.class == 'likeme'){
                    $('#like a').attr('data-original-title','$strNotLike');
                }else {
                    $('#like a').attr('data-original-title','$strLike');
                }
              }
            }
      
          }
        });
  });
});
JS;


$this->registerJs($js);
?>

    <div class="container-like">
        <div class="like-wrap-button">
            <?php if (\Yii::$app->user->isGuest) { ?>
                <span class="<?= $lme ?>" data-toggle="tooltip" title="<?= BaseAmosModule::t('amosapp', 'Accedi/Registrati per mettere Mi piace') ?>">
                        <span><?= AmosIcons::show('heart', [], 'dash') ?></span>
                </span>
            <?php } else { ?>
                <span id="like" class="<?= $lme ?>">

                    <a class="btn" data-toggle="tooltip" title="<?= $labelLike ?>"><?= AmosIcons::show('heart', [], 'dash') ?></a>
                </span>
            <?php } ?>

            <span class="like-wrap-piacea">
                <?php if (\Yii::$app->user->isGuest) { ?>
                    <span data-toggle="tooltip" title="<?= BaseAmosModule::t('amosapp', 'Accedi/Registrati per vedere chi ha messo Mi piace') ?>"><?= BaseAmosModule::t('amosapp', 'Piace a') . ' ' ?>
                        &thinsp;<span id="n-piacea" class="likeme"><?= $tot ?></span>&thinsp;
                        <?= ' ' . BaseAmosModule::t('amosapp', 'utenti') ?>
                    </span>
                <?php } else { ?>
                    <a class="btn" id="like-to" data-toggle="tooltip" title="<?= BaseAmosModule::t('amosapp', 'Visualizza chi ha messo mi piace') ?>"><?= BaseAmosModule::t('amosapp', 'Piace a') . ' ' ?>
                        &thinsp;<span id="n-piacea" class="likeme"><?= $tot ?></span>&thinsp;
                        <?= ' ' . BaseAmosModule::t('amosapp', 'utenti') ?>
                    </a>
                <?php } ?>
            </span>
        </div>
        <div hidden id="like-uid" data-classname="" data-key="<?= $uid ?>"></div>
        <div hidden id="like-cid" data-classname="" data-key="<?= $cid ?>"></div>
        <div hidden id="like-mid" data-classname="" data-key="<?= $mid ?>"></div>
    </div>

<?php
Modal::begin([
    'id' => 'openModelUsers',
    'header' => AmosIcons::show('heart', [], 'dash') . ' ' . BaseAmosModule::t('app', 'Piace a'),
//    'size' => Modal::SIZE_LARGE
]);
echo Html::tag('div', '', ['id' => 'openmodal-preview']);
Modal::end();
?>