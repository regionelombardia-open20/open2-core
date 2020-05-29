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
namespace <?= $data_obj->ns_4class ?>;

use open20\amos\core\widget\WidgetIcon;
use Yii;
use yii\helpers\ArrayHelper;

class <?= $data_obj->widgetName ?> extends WidgetIcon {

    public function init() {
        parent::init();

        $this->setLabel(\Yii::t('<?= $data_obj->ns_4class; ?>' , '<?= $data_obj->widgetLabel; ?>'));
        $this->setDescription(Yii::t('<?= $data_obj->ns_4class; ?>', '<?= $data_obj->widgetDescription ?>'));

        $this->setIcon('<?= $data_obj->iconClass; ?>');
        $this->setIconFramework('<?= $data_obj->iconFramework; ?>');


        $this->setUrl(Yii::$app->urlManager->createUrl(['<?= $data_obj->widgetUrl ?>']));
        $this->setModuleName('<?= $data_obj->moduleName ?>');
        $this->setNamespace(__CLASS__);
        $this->setClassSpan(ArrayHelper::merge($this->getClassSpan(), [
            'bk-backgroundIcon',
            '<?= $data_obj->iconColor; ?>'
        ]));
    }

}