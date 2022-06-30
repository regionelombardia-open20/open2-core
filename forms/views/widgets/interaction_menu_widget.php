<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\forms\views\widgets
 * @category   CategoryName
 */

/**
 * @var bool $hideInteractionMenu If true set the class that hide the interaction menu.
 * @var string $interactionMenuButtons The HTML to render the interaction menu buttons.
 */
?>

<div class="interaction-widget<?= ($hideInteractionMenu ? ' hidden' : '') ?>">
    <?= $interactionMenuButtons ?>
</div>