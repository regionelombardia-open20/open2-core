<?=
"<?php
use lispa\amos\core\icons\AmosIcons;
/*
 * Personalizzare a piacimento la vista
 * \$model è il model legato alla tabella del db
 * \$buttons sono i tasti del template standard {view}{update}{delete}
 */



?>

<div class=\"row list-horizontal-element\">

    <div class=\"list-element-left\">
        <div class=\"grow-pict\">
            <img class=\"img-responsive img-round\" src=\"<?= \$model->getAvatarUrl('medium') ?>\" alt=\"<?= \$model ?>\" />
        </div>
    </div>

    <div class=\"list-element-right\">
        <div class=\"container-btn\">
            <button class=\"btn btn-navigation-primary\">
                <?= AmosIcons::show('link'); ?>
                <span class=\"hidden-xs\">LINK</span>
            </button>
        </div>
        <div class =\"list-element-body\">
            <h3><a href=\"#\"><?= \$model ?></a></h3>
            <?php if (\$model->getTelefonoDefault()): ?>
            <p>
                <?= AmosIcons::show('phone'); ?>
                <span><?= \$model->getTelefonoDefault() ?></span>
            </p>
            <?php
            endif;
            if (\$model->getTestoIcon()):
            ?>
            <p>
                <?= AmosIcons::show('home'); ?>
                <?= \$model->getTestoItem() ?>
            </p>
            <?php
            endif;
            if (\$model->getEmailDefault()):
            ?>            
            <p>
                <?= AmosIcons::show('email'); ?>
                <span><?= \$model->getEmailDefault() ?></span>
            </p>
            <?php endif; ?>           
        </div>                 
        <div class=\"foot-bar row\">
            <?php if(\$model->getContattiComune()): ?>
            <div class=\"col-sm-7 col-xs-12 foot-bar-left\">
                <p><?= \$model->getContattiComune() ?></p>
            </div>
            <div class=\"col-sm-5 col-xs-12 foot-bar-right\">
                <p>
                    <?= \$buttons ?>
                </p>
            </div>
            <?php else: ?>
            <div class=\"col-xs-12 foot-bar-right\">
                <p>
                    <?= \$buttons ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>"
?>
