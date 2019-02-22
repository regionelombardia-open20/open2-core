<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\giiamos\widgets\default
 * @category   CategoryName
 */

echo "<?php\n";
?>
use lispa\amos\core\migration\AmosMigrationWidgets;
use lispa\amos\dashboard\models\AmosWidgets;


/**
* Class <?= $data_obj->migration_widget_filename; ?>
*/
class <?= $data_obj->migration_widget_filename; ?> extends AmosMigrationWidgets
{
    const MODULE_NAME = '<?= $data_obj->moduleName; ?>';

    /**
    * @inheritdoc
    */
    protected function initWidgetsConfs()
    {
        $this->widgets = [
            [
                'classname' => \<?= $data_obj->ns_4class. '\\' .$data_obj->widgetName; ?>::className(),
                'type' => <?php if(strtolower($data_obj->widgetType) == 'icon'): ?>AmosWidgets::TYPE_ICON<?php else: ?>AmosWidgets::TYPE_GRAPHIC <?php endif;?>,
                'module' => self::MODULE_NAME,
                'status' => AmosWidgets::STATUS_ENABLED,
                'dashboard_visible' => <?= $data_obj->widgetVisible; ?>,
                <?php if(!empty($data_obj->widgetFather)): ?>'child_of' => \<?= $data_obj->widgetFather ?>::className(),<?php endif; ?>

            ]
        ];
    }
}
