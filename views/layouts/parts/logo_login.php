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
use lispa\amos\core\utilities\CurrentUser;
?>

<?php if (isset(Yii::$app->params['logo'])): ?>
<div class="logo-login" role="banner">
    <div class="img-logo text-center">
    <?=
    Html::a(Html::img(((isset(Yii::$app->params['logo'])) ? Yii::$app->params['logo'] : '/img/logo.png'), [
        'class' => 'img-responsive logo-amos',
        'alt' => 'logo '. Yii::$app->name
    ]), [Yii::$app->homeUrl,],  ['title' => 'vai alla home page']);
    ?>
    </div>
</div>
<?php elseif (isset(Yii::$app->params['logo-signature'])): ?>
<div class="logo-login" role="banner">
    <div class="img-logo text-center">
    <?=
    Html::a(Html::img(Yii::$app->params['logo-signature'], [
        'class' => 'img-responsive signature',
        'alt' => 'logo firma'
    ]), [Yii::$app->homeUrl,]);
    ?>
    </div>
</div>
<?php elseif (isset(Yii::$app->params['logo-text']) && \Yii::$app->params['logo-text'] == TRUE): ?>
    <div class="login-logo-text">
        <?=
        Html::a('<h2>'.Yii::$app->params['logo-text'].'</h2>',
            [Yii::$app->homeUrl,],
            ['class' => 'logo-text']
        );
        ?>
    </div>
<?php else: ?>
    <!--div class="login-logo-text"-->
        <?php
        /*Html::a('<h1>'.strtoupper(\Yii::$app->name).'</h1>',
            [Yii::$app->homeUrl,]
        );*/
        ?>
    <!--/div-->
<?php endif; ?>