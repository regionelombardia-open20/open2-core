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
<?php  ?>
namespace <?= $data_obj->ns_4class ?>;

use lispa\amos\core\widget\WidgetGraphic;
use Yii;
use yii\helpers\ArrayHelper;

class WidgetGraphics<?= $data_obj->widgetName?> extends WidgetGraphic {

    public function getHtml() {

    }

    public function init() {
        parent::init();

        $this->setLabel(\Yii::t('<?= $data_obj->ns_4class; ?>' , '<?= $data_obj->widgetLabel; ?>'));
        $this->setDescription(Yii::t('<?= $data_obj->ns_4class; ?>', '<?= $data_obj->widgetDescription ?>'));
    }

}