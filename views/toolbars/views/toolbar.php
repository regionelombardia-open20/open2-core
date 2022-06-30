<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views\toolbars\views
 * @category   CategoryName
 */

use open20\amos\core\icons\AmosIcons;
use open20\amos\core\helpers\Html;
use open20\amos\core\views\toolbars\StatsToolbar;

/**
 *
 * @var $panels array
 */

?>

<?php if ($layoutType == StatsToolbar::LAYOUT_DEFAULT): ?>
    <div class="shared stats-toolbar">
        <table class="pull-left">
            <tr>
                <?php foreach ($panels as $panel): ?>
                    <td>
                        <?= $panel->render($onClick) ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        </table>
    </div>
<?php elseif ($layoutType == StatsToolbar::LAYOUT_VERTICAL): ?>
    <?php
    $content = '';
    foreach ($panels as $panel) {
        $content .= Html::tag('span', $panel->render($onClick), ['class' => 'item']);
    }
    echo Html::tag('div', $content, ['class' => 'stats-toolbar toolbar-vertical']);
    ?>
<?php elseif ($layoutType == StatsToolbar::LAYOUT_HORIZONTAL): ?>
    <?php
    $content = '';
    foreach ($panels as $panel) {
        $content .= Html::tag('span', $panel->render($onClick), ['class' => 'item']);
    }
    echo Html::tag('div', $content, ['class' => 'stats-toolbar toolbar-horizontal']);
    ?>
<?php endif; ?>