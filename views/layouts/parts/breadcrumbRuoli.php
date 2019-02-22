<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views\layouts\parts
 * @category   CategoryName
 */

use yii\widgets\Breadcrumbs;
use lispa\amos\core\helpers\Html;

$urlCorrente = yii\helpers\Url::current();
$posizioneEsclusione = strpos($urlCorrente, '?');
$urlParametro = $urlCorrente;
if ($posizioneEsclusione > 0) {
    $urlParametro = preg_quote(substr($urlCorrente, 0, $posizioneEsclusione));
}
$urlFaq = Yii::$app->getUrlManager()->createUrl(['/faq/faq', 'FaqSearch[rotte]' => $urlParametro]);
?>


    <div class="breadcrumb_left">
        <?=
        Breadcrumbs::widget([
            'homeLink' => [
                'label' => Yii::t('amoscore', 'Dashboard'),
                'url' => Yii::$app->homeUrl,
                'encode' => false
            ],
            'links' => [
//                [
//                    'label' => 'Classi Utenti e Ruoli',
//                    'url' => '/classiutenti/classi-utenti'
//                ],
                [
                    'label' => isset($this->params['breadcrumbs']) ? $this->title : '',
                ],               
            ]
        ])
        ?>
    </div>
<!--    <div id="bk-generalTools" class="show breadcrumb-tools">
        <a href="<?php // $urlFaq; ?>"><button>
                <span>FAQ</span><span class="sr-only">Leggi le faq relative al plugin</span>
            </button></a>
    </div>-->


