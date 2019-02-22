<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views\layouts
 * @category   CategoryName
 */

/* @var $this \yii\web\View */
/* @var $content string */
//\bedezign\yii2\audit\web\JSLoggingAsset::register($this);
?>


<?php $this->beginPage() ?>

<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <?= $this->render("parts" . DIRECTORY_SEPARATOR . "head"); ?>
</head>
<body>

<?php $this->beginBody() ?>

<div class="login-page col-lg-4 col-md-6 col-sm-6 col-xs-12 col-lg-push-4 col-md-push-3 col-sm-push-3 nop">

    <?= $this->render("parts" . DIRECTORY_SEPARATOR . "messages"); ?>

    <div class="col-xs-12 dropdown-languages">
        <?php
        $headerMenu = new \lispa\amos\core\views\common\HeaderMenu();
        $menuLang = $headerMenu->getListLanguages();
        echo $menuLang;
        ?>
    </div>
    <div class="clearfix"></div>

    <?= $this->render("parts" . DIRECTORY_SEPARATOR . "logo_login"); ?>

    <?= $content ?>

</div>

<div class="clearfix"></div>
<!--< ?= $this->render("parts" . DIRECTORY_SEPARATOR . "sponsors"); ?>-->
<?= $this->render("parts" . DIRECTORY_SEPARATOR . "footer"); ?>

<?php $this->endBody() ?>

</body>
</html>
<?php $this->endPage() ?>
