<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views\layouts
 * @category   CategoryName
 */

\bedezign\yii2\audit\web\JSLoggingAsset::register($this);
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var \lispa\amos\core\components\PartQuestionarioAbstract $partsQuestionario */
/* @var $content string */

$urlCorrente = Url::current();
$arrayUrl = explode('/', $urlCorrente);
$countArrayUrl = count($arrayUrl);
$percorso = '';
$i = 0;
$moduloId = Yii::$app->controller->module->id;
$basePath = Yii::$app->getBasePath();
if ($moduloId != 'app-backend') {
    $percorso .= '/modules/' . $moduloId . '/views/' . $arrayUrl[$countArrayUrl - 2];
} else {
    $percorso .= 'views';
    while ($i < ($countArrayUrl - 1)) {
        $percorso .= $arrayUrl[$i] . '/';
        $i++;
    }
}
if ($countArrayUrl) {
    $posizioneEsclusione = strpos($arrayUrl[$countArrayUrl - 1], '?');
    if ($posizioneEsclusione > 0) {
        $vista = substr($arrayUrl[$countArrayUrl - 1], 0, $posizioneEsclusione);
    } else {
        $vista = $arrayUrl[$countArrayUrl - 1];
    }
    if (file_exists($basePath . '/' . $percorso . '/help/' . $vista . '.php')) {
        $this->params['help'] = [
            'filename' => $vista
        ];
    }
}

$script = <<< SCRIPT
$(document).ready(function (){

    setTimeout(function (){

        var errori = $('.error-regionale');
        if($(errori).length){
            $(".error-summary-fake").fadeIn();
        }else{
            $(".error-summary-fake").fadeOut();
        }

    }, 500 );

    $('body').on('afterValidate', 'form' , function (){

        setTimeout(function (){
            var errori = $('.error-regionale');
                if($(errori).length){
                    $(".error-summary-fake").fadeIn();
                }else{
                    $(".error-summary-fake").fadeOut();
                }
        },500);

    });

    $('body').on('change', 'input' , function (){

        setTimeout(function (){
            var errori = $('.error-regionale');
                if(!$(errori).length){
                    $(".error-summary-fake").fadeOut();
                }
        },500);


    });

});
SCRIPT;

$this->registerJs($script, \yii\web\View::POS_END, 'my-options');

?>


<?php $this->beginPage() ?>

<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <?= $this->render("parts" . DIRECTORY_SEPARATOR . "head"); ?>
</head>
<body>

<?php $this->beginBody() ?>

<div class="container-header">
    <?= $this->render("parts" . DIRECTORY_SEPARATOR . "header"); ?>
</div>
<div class="container-logo">
    <?= $this->render("parts" . DIRECTORY_SEPARATOR . "logo"); ?>
</div>

<?php if (isset(Yii::$app->params['logo-bordo'])): ?>
    <div class="container-bordo-logo"><img src="<?= Yii::$app->params['logo-bordo'] ?>" alt=""></div>
<?php endif; ?>

<section id="bk-page">
    <div class="container-messages">
        <div class="container">
            <?= $this->render("parts" . DIRECTORY_SEPARATOR . "messages"); ?>
        </div>
    </div>

    <div class="container-help">
        <div class="container">
            <?= $this->render("parts" . DIRECTORY_SEPARATOR . "help"); ?>
        </div>
    </div>

    <div class="container">
        <div class="page-content">
            <?php if (!isset($this->params['hideBreadcrumb']) || ($this->params['hideBreadcrumb'] === false)): ?>
                <?= $this->render("parts" . DIRECTORY_SEPARATOR . "breadcrumb"); ?>
            <?php endif; ?>
            <div class="page-header">
                <?php if (!isset($this->params['hideWizardTitle']) || ($this->params['hideWizardTitle'] === false)): ?>
                    <h1 class="title"><?= Html::encode($this->title) ?></h1>
                <?php endif; ?>
                <?= $this->render("parts" . DIRECTORY_SEPARATOR . "textHelp"); ?>
            </div>
            <div class="col-sm-12 progress-menu-container">
                <?= $this->render("parts" . DIRECTORY_SEPARATOR . "progress_wizard_menu", [
                    'model' => $this->params['model'],
                    'partsQuestionario' => $this->params['partsQuestionario'],
                    'hidePartsLabel' => (isset($this->params['hidePartsLabel']) ? $this->params['hidePartsLabel'] : false),
                    'hidePartsUrl' => (isset($this->params['hidePartsUrl']) ? $this->params['hidePartsUrl'] : false)
                ]);
                ?>
            </div>
            <div class="col-sm-12">
                <div class="error-summary-fake" style="display: none;">
                    <?php
                    \yii\bootstrap\Alert::begin([
                        'closeButton' => false,
                        'options' => [
                            'class' => 'danger alert-danger error-summary',
                        ],
                    ]);
                    \yii\bootstrap\Alert::end();
                    ?>
                </div>
            </div>
            
            <?= $content ?>

        </div>
    </div>
</section>

<?= $this->render("parts" . DIRECTORY_SEPARATOR . "sponsors"); ?>
<?= $this->render("parts" . DIRECTORY_SEPARATOR . "footer"); ?>

<?php $this->endBody() ?>

</body>
</html>
<?php $this->endPage() ?>
