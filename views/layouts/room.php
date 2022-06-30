<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views\layouts
 * @category   CategoryName
 */

use open20\amos\core\components\AmosView;
use yii\helpers\Html;
use yii\helpers\Url;

////\open20\amos\audit\web\JSLoggingAsset::register($this);

/** @var $this \open20\amos\core\components\AmosView */
/** @var $content string */

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
            <div class="page-content room-page-content">
                <?= $this->render("parts" . DIRECTORY_SEPARATOR . "breadcrumb"); ?>
                <div class="page-header">
                    <h1 class="title"><?= (strlen(Html::encode($this->title)) > 100) ? substr(Html::encode($this->title),0,95).'[...]' : Html::encode($this->title) ?></h1>
                    <?= $this->render("parts" . DIRECTORY_SEPARATOR . "textHelp"); ?>
                </div>
                <?php if ($this instanceof AmosView): ?>
                    <?php $this->beginViewContent() ?>
                <?php endif; ?>
                <?= $content ?>
                <?php if ($this instanceof AmosView): ?>
                    <?php $this->endViewContent() ?>
                <?php endif; ?>
                <!--        </div> <!-- close div in amos-community view/join/index.php -->
                <?php //TODO cercare una soluzione per le aree di lavoro/room ?>
        </div>
    </section>

    <?= $this->render("parts" . DIRECTORY_SEPARATOR . "sponsors"); ?>
    <?= $this->render("parts" . DIRECTORY_SEPARATOR . "footer"); ?>
    
    <?php $this->endBody() ?>

    </body>
    </html>
<?php $this->endPage() ?>