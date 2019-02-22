<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\forms\views
 * @category   CategoryName
 */

use lispa\amos\core\module\BaseAmosModule;
use lispa\amos\core\forms\ActiveForm;
use lispa\amos\core\helpers\Html;
use yii\bootstrap\Modal;

/**
 * @var \lispa\amos\core\forms\EmailForm $infoRequest
 * @var integer $modelId
 */

/** @var \lispa\amos\core\controllers\CrudController $controller */
$controller = Yii::$app->controller;

/** @var \lispa\amos\core\module\AmosModule $controller->module */
$url = '/'. $controller->module->getUniqueId().'/'.$controller->id .'/request-information?id='.$modelId;
$formErrorMessage = BaseAmosModule::t('amoscore', '#info_request_form_error');

$js = <<<JS

$("#info-request-form").submit(function(e) { 
    if ($(this).data('submitted') === true) {
      // Previously submitted - don't submit again
      e.preventDefault();
      return false;
    } else {
      // Mark it so that the next submit can be ignored
      $(this).data('submitted', true);
    }
    var postdata = $(this).serializeArray();
    var formurl = $(this).attr("action");
    $.ajax( {
        url : formurl,
        type: "POST", 
        data : postdata, 
        success:function(data, textStatus, jqXHR) { //data: returning of data from the server
            $('#info-request-modal').modal('hide');
            $('#response-text').text(data);
            $('#info-request-response-modal').modal('show');
          if(!$('#form-errors').hasClass('hidden')){
            $('#form-errors').addClass('hidden');
         }
        }, 
        error: function(jqXHR, textStatus, errorThrown) { 
            console.log(errorThrown); 
        }
    });
    e.preventDefault(); // default action us stopped here 
    return false;
}); 

$('#send-info-request').on('click', function(e) {
    e.preventDefault();
    var message = ($('#emailForm-message').val().length);
     if(message){
         if(!errors.hasClass('hidden')){
            errors.addClass('hidden');
         }
         return true;
     }else {
          errors.removeClass('hidden');
           return false;
     }
});

$( "#info-request-form" ).submit(function( event ) {
  $('#my-modal').modal('hide');
});
    
JS;
$this->registerJs($js, \yii\web\View::POS_READY);

Modal::begin([
    'header' => BaseAmosModule::t('amoscore', '#info_request_modal_title'),
    'id' => 'info-request-modal',
    'size' => Modal::SIZE_LARGE
]);

?>

    <div id="info-request-container">

        <?php $form = ActiveForm::begin(['id' => 'info-request-form', 'action' => $url]); ?>
        <div class="col-lg-12 col-sm-12 alert alert-danger hidden " id="form-errors"><?= $formErrorMessage ?></div>

        <div class="hidden">
            <?= $form->field($infoRequest, 'userIdTo')->hiddenInput(['value' => $infoRequest->userIdTo])->label(false) ?>
            <?= $form->field($infoRequest, 'attributeTo')->hiddenInput(['value' => $infoRequest->attributeTo])->label(false) ?>
            <?= $form->field($infoRequest, 'templatePath')->hiddenInput(['value' => $infoRequest->templatePath])->label(false) ?>
            <?= $form->field($infoRequest, 'subject')->hiddenInput(['value' => $infoRequest->subject])->label(false) ?>
        </div>

        <div class="row">
            <div class="col-lg-12 col-sm-12">
                <?= $form->field($infoRequest, 'message')->widget(\yii\redactor\widgets\Redactor::className(), [
                    'options' => [
                        'placeholder' => BaseAmosModule::t('amoscore', '#info_request_placeholder')
                    ],
                    'clientOptions' => [
                        'buttonsHide' => [
                            'image',
                            'file'
                        ],
                        'lang' => substr(Yii::$app->language, 0, 2)
                    ]
                ]) ?>
            </div>
        </div>

        <div class='bk-btnFormContainer'>
            <?= Html::submitButton(BaseAmosModule::t('amoscore', '#send_email'), [
                'class' => 'btn btn-primary',
                'id' => 'send-infoRequest',
            ]) ?>
            <?= Html::a(BaseAmosModule::t('amoscore', 'Annulla'), null,
                ['class' => 'btn btn-secondary', 'data-dismiss' => 'modal']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

<?php
Modal::end();

Modal::begin([
    'header' => BaseAmosModule::t('amoscore', '#info_request_modal_title'),
    'id' => 'info-request-response-modal',
]);
?>
    <div id="response-text" class="m-b-30">
        <!-- filled by javascript -->
    </div>
<?php
Modal::end();
?>
