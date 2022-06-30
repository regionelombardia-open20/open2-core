<?php

use open20\amos\core\helpers\Html;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\module\BaseAmosModule;

use yii\bootstrap\Modal;
$module = \Yii::$app->controller->module->id;
$controller = \Yii::$app->controller->id;

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
      <span id="like" class="<?= $lme ?>">
          <a class="btn"><?= AmosIcons::show('heart', [], 'dash') ?></a>
      </span>
        <span class="like-wrap-piacea">
          <a class="btn" id="like-to"><?= BaseAmosModule::t('amosapp','Piace a') . ' ' ?><span id="n-piacea" class="likeme">
                  <?= $tot ?>
              </span><?= ' ' . BaseAmosModule::t('amosapp', 'utenti') ?>
          </a>
  </span>
    </div>
    <div hidden id="like-uid" data-classname="" data-key="<?= $uid ?>"></div>
    <div hidden id="like-cid" data-classname="" data-key="<?= $cid ?>"></div>
    <div hidden id="like-mid" data-classname="" data-key="<?= $mid ?>"></div>
</div>

<?php
Modal::begin([
    'id' => 'openModelUsers',
    'header' => AmosIcons::show('heart', [], 'dash') . ' ' .BaseAmosModule::t('app', 'Piace a'),
//    'size' => Modal::SIZE_LARGE
]);
echo Html::tag('div', '', ['id' => 'openmodal-preview']);
Modal::end();
?>