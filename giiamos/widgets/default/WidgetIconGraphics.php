<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\giiamos\widgets\default
 * @category   CategoryName
 */

echo "<?php\n";
?>
<?php  ?>
namespace <?= $data_obj->ns_4class ?>;

use open20\amos\core\widget\WidgetGraphic;
use Yii;
use yii\helpers\ArrayHelper;
use open20\amos\core\module\BaseAmosModule;

class WidgetGraphics<?= $data_obj->widgetName?> extends WidgetGraphic {

    public function getHtml() {

    }

    public function init() {
        parent::init();

        $this->setLabel(BaseAmosModule::t('<?= $data_obj->ns_4class; ?>' , '<?= $data_obj->widgetLabel; ?>'));
        $this->setDescription(BaseAmosModule::t('<?= $data_obj->ns_4class; ?>', '<?= $data_obj->widgetDescription ?>'));
    }

}