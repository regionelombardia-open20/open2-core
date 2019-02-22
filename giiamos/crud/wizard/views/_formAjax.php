<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\giiamos\crud\wizard\views
 * @category   CategoryName
 */

use yii\helpers\Inflector;
use yii\helpers\StringHelper;
 
/**
 * @var yii\web\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */
/** @var \yii\db\ActiveRecord $model */
$model = new $generator->modelClass;
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

$itemsTab = [];
$arrClassModel = explode('\\', $generator->modelClass);
$classModel = end($arrClassModel);

echo "<?php\n";
?>

use lispa\amos\core\helpers\Html;
use lispa\amos\core\forms\ActiveForm;
use kartik\builder\Form;
use kartik\datecontrol\DateControl;
use lispa\amos\core\forms\Tabs;
use lispa\amos\core\forms\CloseSaveButtonWidget;
use yii\helpers\Url;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use lispa\amos\core\icons\AmosIcons;
use yii\bootstrap\Modal;

/**
* @var yii\web\View $this
* @var <?= ltrim($generator->modelClass, '\\') ?> $model
* @var yii\widgets\ActiveForm $form
*/
?>
<?php
$ajaxForm = FALSE;
$descriptionAttribute = "";
foreach ((array) $generator->mmRelations as $Relation){
    if (Inflector::id2camel($Relation['toEntity'], '_') == $classModel){
        $ajaxForm = TRUE;
        $descriptionAttribute = ucfirst($Relation['descriptorField']);
        continue;
    }
}
?>
<?= "<?=" ?> $this->render('_form', [
    'model' => $model,
    'fid' => (NULL !== (filter_input(INPUT_GET, 'fid')))? filter_input(INPUT_GET, 'fid') : '',
    'dataField' => (NULL !== (filter_input(INPUT_GET, 'dataField')))? filter_input(INPUT_GET, 'dataField') : '',
    'dataEntity' => (NULL !== (filter_input(INPUT_GET, 'dataEntity')))? filter_input(INPUT_GET, 'dataEntity') : '',
    'class' => 'dynamicCreation'
]) <?= "?>" ?>