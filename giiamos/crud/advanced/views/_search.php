<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\giiamos\crud\wizard\views
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
echo "<?php\n".
 "/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    " . StringHelper::dirname(ltrim($generator->viewPath, '\\')) . "/views" . " 
 */";

$arrClassModel = explode('\\', $generator->modelClass);
$classModel = end($arrClassModel);

$campis = [];
foreach ($generator->getFormTabsAsArray() as $nomeTab) {
    foreach ($generator->getAttributesTab($nomeTab) as $attribute) {
        $colonna = $model->getTableSchema()->getColumn($attribute);
        if (isset($colonna) && $colonna->type == 'date') {
            $campis['date'][] = $colonna->name;
        }
    }
}
?>

use open20\amos\core\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\datecontrol\DateControl;

/**
* @var yii\web\View $this
* @var <?= ltrim($generator->searchModelClass, '\\') ?> $model
* @var yii\widgets\ActiveForm $form
*/

<?php if (isset($campis['date'])): ?>
    $this->registerJs("
    <?php foreach ($campis['date'] as $dateArr) { ?>
        $('#<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>search-<?= $dateArr ?>').change(function(){
        if($('#<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>search-<?= $dateArr ?>').val() == ''){
        $('#<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>search-<?= $dateArr ?>-disp-kvdate .input-group-addon.kv-date-remove').remove();
        } else {
        if($('#<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>search-<?= $dateArr ?>-disp-kvdate .input-group-addon.kv-date-remove').length == 0){
        $('#<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>search-<?= $dateArr ?>-disp-kvdate').append('<span class=\"input-group-addon kv-date-remove\" title=\"Pulisci campo\"><i class=\"glyphicon glyphicon-remove\"></i></span>');
        initDPRemove('<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>search-<?= $dateArr ?>-disp');
        }
        }
        });
    <?php } ?>
    ", yii\web\View::POS_READY);
<?php endif; ?>

?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-search element-to-toggle" data-toggle-element="form-search">

    <?= "<?php " ?>$form = ActiveForm::begin([
    'action' => (isset($originAction) ? [$originAction] : ['index']),
    'method' => 'get',
    'options' => [
    'class' => 'default-form'
    ]
    ]);
    ?>

    <?php
    //file_put_contents('/tmp/pivg', $generator->ordinalFields, FILE_APPEND);
//    foreach ($generator->ordinalFields as $ordinalField) {
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // FIELDS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    foreach ($generator->getColumnNames() as $attribute) {


        $column = $model->getTableSchema()->getColumn($attribute);
//            pr($column);die;
        if (!get_class($column) || $column == null) {
            continue;
        }
//            pr($attribute);
//            pr($generator->getArrayForeignKeys());
//            die;

        echo "<!-- " . $attribute . " -->";
        if (!empty($generator->campiIndex)) {
            //TODO
        } else {
            //if (++$count < 9 && !(in_array($attribute, ['id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by']))) {
            if (!(in_array($attribute, ['id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by']))) {
                if (isset($colonna) && $column->type == 'date') {
                    echo '<!-- DATE -->';
                    echo "\n<div class=\"col-md-4\">\n\t\t\t<?= \$form->field(\$model, '" . $column->name . "')->widget(DateControl::classname(), [
                           \t\t\t\t'options' => [ 'layout' => '{input} {picker} ' . ((\$model->" . $column->name . " == '')? '' : '{remove}')]
                        \t\t\t]); ?>\n</div>\n";
                } else {
                    echo "\n<div class=\"col-md-4\"> <?= \n" . $generator->generateActiveSearchField($attribute) . "->textInput(['placeholder' => 'ricerca per " . addslashes(strtolower(Inflector::camel2words($attribute))) . "' ]) ?>\n\n </div> \n\n";
                }
            } else {
                echo "  <?php // echo " . $generator->generateActiveSearchField($attribute) . " ?>\n\n ";
            }
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // RELATIONS
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ((in_array($attribute, $generator->getArrayForeignKeys()))) {
            $Relation = (array) $generator->getMmRelationsSingle($attribute);
            if (!empty($Relation)) {
                $arrayAttributi = [];
                $indx = [];
                if (Inflector::id2camel($Relation['fromEntity'], '_') == $classModel) {
                    $attributoEntita = $Relation['toEntity'];
                    if (!(in_array($attributoEntita, $arrayAttributi))) {
                        $arrayAttributi[] = $attributoEntita;
                        $indx[$attributoEntita] = 0;
                        echo '
                <div class="col-md-4">
                    <?= 
                    $form->field($model, \'' . lcfirst(Inflector::id2camel($attributoEntita, '_')) . '\')->textInput([\'placeholder\' => \'ricerca per '
                        . strtolower(Inflector::camel2words(ucfirst(addslashes($Relation['descriptorField'])))) . '\'])->label(\'' . ucfirst(addslashes($Relation['descriptorField'])) . '\');
                     ?> 
                </div>
                ';
                    } else {
                        $indx[$attributoEntita] = $indx[$attributoEntita] + 1;
                        $newIndx = $indx[$attributoEntita] - 1;
                        $newAttributoEntita = $attributoEntita . $newIndx;
                        echo '
                <div class="col-md-4">
                    <?= 
                    $form->field($model, \'' . lcfirst(Inflector::id2camel($newAttributoEntita, '_')). '\')->textInput([\'placeholder\' => \'ricerca per '
                        . strtolower(Inflector::camel2words(ucfirst(addslashes($Relation['descriptorField'])))) . '\'])->label(\'' . ucfirst(addslashes($Relation['descriptorField'])) . '\');
                    ?>
                </div>
                ';
                    }
                }
            }
        }
    }
    ?>
    <div class="col-xs-12">
        <div class="pull-right">
            <?= "<?= " ?>Html::resetButton(<?= $generator->generateString('Reset') ?>, ['class' => 'btn btn-secondary']) ?>
            <?= "<?= " ?>Html::submitButton(<?= $generator->generateString('Search') ?>, ['class' => 'btn btn-navigation-primary']) ?>
        </div>
    </div>

    <div class="clearfix"></div>

    <?= "<?php " ?>ActiveForm::end(); ?>
</div>
