<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms\views\widgets
 * @category   CategoryName
 */

use open20\amos\core\icons\AmosIcons;
use open20\amos\core\module\BaseAmosModule;

/**
 * @var boolean $atLeastOneElement
 * @var array $buttons
 * @var string $mainDivClasses
 */

?>
<div class="btn-group">
    <div class="<?= $mainDivClasses ?> content-settings-menu" data-toggle="dropdown" href="" aria-expanded="true"
         title="<?= BaseAmosModule::t('amoscore', '#content_settings_menu_label') ?>">
        <?= AmosIcons::show('settings', ['class' => 'pull-left']) ?>
        <?= AmosIcons::show('chevron-down', ['class' => 'pull-right']) ?>
        <span class="sr-only"><?= BaseAmosModule::t('amoscore', '#content_settings_menu_label') ?></span>
    </div>
    <ul class="dropdown-menu pull-right">
        <?php foreach ($buttons as $button): ?>
            <li><?= $button ?></li>
        <?php endforeach; ?>
    </ul>
</div>

