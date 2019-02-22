<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views\layouts
 * @category   CategoryName
 */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use frontend\widgets\Alert;
use backend\assets\ModuleFrontendAsset;

/* @var $this \yii\web\View */
/* @var $content string */

//\bedezign\yii2\audit\web\JSLoggingAsset::register($this);
AppAsset::register($this);
ModuleFrontendAsset::register($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>

<html lang="<?= Yii::$app->language ?>">

    <head>          
        <?php $this->head() ?>        
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <meta charset="UTF-8">
        <title>Pane e Internet</title>
        <link rel="stylesheet" href="/frontend/css/bootstrap.min.css" />
        <link rel="stylesheet" href="/frontend/css/flexslider.css" />
        <link rel="stylesheet" href="/frontend/css/bootstrap-responsive-tabs.css" />
        <link rel="stylesheet" href="/frontend/css/style.css" />
        <link rel="stylesheet" href="/frontend/css/fonts.css" />
        <script type="text/javascript" src="//code.jquery.com/jquery-1.11.3.min.js"></script>
        <script type="text/javascript" src="/frontend/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="/frontend/js/modernizr.custom.62816.js"></script> 
        <script type="text/javascript" src="/frontend/js/jquery.flexslider-min.js"></script> 
        <script type="text/javascript" src="/frontend/js/jquery.bootstrap-responsive-tabs.min.js"></script>                   <script type="text/javascript">
    window.cookieconsent_options = {"message":"Per offrire informazioni e servizi nel miglior modo possibile, questo sito utilizza cookie tecnici e cookie di terze parti.","dismiss":"Ok!","learnMore":"Per maggiori informazioni","link":"//www.paneeinternet.it/public/policy-privacy","theme":"dark-bottom"};
        </script>  


        <!--[if lt IE 9]>
        <script src="/frontend/js/html5shiv.js"></script>
        <script src="/frontend/js/respond.js"></script> 
        <![endif]-->
        <title><?= Html::encode(Yii::$app->name) ?></title>

    </head>
    <body>
        <?php $this->beginBody() ?>

        <a href="#mainContent" class="sr-only sr-only-focusable">Skip to main content</a> 
        
        <?= $content ?>              
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>

