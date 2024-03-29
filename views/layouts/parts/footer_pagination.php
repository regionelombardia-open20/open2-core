<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views\layouts\parts
 * @category   CategoryName
 */

use open20\amos\core\icons\AmosIcons;
use yii\helpers\Html;
use open20\amos\core\module\BaseAmosModule;

//Pickup assistance params
$assistance = isset(\Yii::$app->params['assistance']) ? \Yii::$app->params['assistance'] : [];

//Check if is in email mode
$isMail = ((isset($assistance['type']) && $assistance['type'] == 'email') || (!isset($assistance['type']) && isset(\Yii::$app->params['email-assistenza']))) ? true : false;
$mailAddress = isset($assistance['email']) ? $assistance['email'] : (isset(\Yii::$app->params['email-assistenza'])? \Yii::$app->params['email-assistenza'] : '');
?>

<?php if (isset(\Yii::$app->params['assistance-url'])):?>
    <div class="assistance">
        <a href="<?=\Yii::$app->params['assistance-url']?>">
            <?=AmosIcons::show('assistance', ['class' => 'icon-assistance'], 'dash')?>
            <span><?=BaseAmosModule::t('amoscore','Hai bisogno di assistenza?')?></span>
            <span class="sr-only"><?=BaseAmosModule::t('amoscore','Verrà aperta una nuova finestra')?></span>
        </a>
    </div>
<?php elseif (isset(Yii::$app->modules['assistance-request'])): ?>
    <?= $this->renderFile('@vendor/open20/amos-assistance-request/src/views/_modal_form_request.php');?>
<?php else: ?>
    <?php if ((isset($assistance['enabled']) && $assistance['enabled']) || (!isset($assistance['enabled']) && isset(\Yii::$app->params['email-assistenza']))): ?>
        <div class="assistance">
            <a href="<?= $isMail ? 'mailto:' . $mailAddress : (isset($assistance['url'])? $assistance['url'] : '') ?>" target="_blank">
                <?=AmosIcons::show('assistance', ['class' => 'icon-assistance'], 'dash')?>
                <span><?=BaseAmosModule::t('amoscore', 'Hai bisogno di assistenza?');?></span>
                <span class="sr-only"><?=BaseAmosModule::t('amoscore', 'Verrà aperta una nuova finestra')?></span>
            </a>
        </div>
    <?php endif; ?>
<?php endif; ?>

<footer>
<!--            TODO CRATE PAGINATION      -->
<!--        <div class="footer-pagination">
        <div class="container">
            <ul class="dashboard-pagination">
                <li class="active" title="dashboard 1"></li>
                <li><a href="#" title="dashboard 2"></a></li>
                <li><a href="#" title="dashboard 3"></a></li>
                <li class="add"><a href="#" title="aggiungi dashboard"><?/*= AmosIcons::show('plus') */?></a></li>
            </ul>
        </div>
    </div>-->

</footer>
<?php
if (\Yii::$app->getModule('social') && class_exists('\kartik\social\GoogleAnalytics')):
    if (YII_ENV_PROD):
        echo \kartik\social\GoogleAnalytics::widget([]);
    endif;
endif;
?>