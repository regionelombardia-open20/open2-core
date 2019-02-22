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
$urlParams = $generator->generateUrlParams();
$arrClassModel = explode('\\', $generator->modelClass);
$classModel = end($arrClassModel);

echo "<?php\n".
 "/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    " . StringHelper::dirname(ltrim($generator->viewPath, '\\')) . "/views" . " 
 */";
?>

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\datecontrol\DateControl;
use yii\helpers\Url;

/**
* @var yii\web\View $this
* @var <?= ltrim($generator->modelClass, '\\') ?> $model
*/

$this->title = strip_tags($model);
$this->params['breadcrumbs'][] = ['label' => '<?= ucfirst($generator->descriptiveNameModule) ?>', 'url' => ['/<?= StringHelper::basename($generator->moduleNs) ?>']];
$this->params['breadcrumbs'][] = ['label' => <?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">

    <?= "<?= " ?>DetailView::widget([
    'model' => $model,    
    'attributes' => [
    <?php
    foreach ($generator->getColumnNames() as $attribute) {
        $column = $generator->getTableSchema()->getColumn($attribute);
        $tableSchema = $generator->getTableSchema();
        if (!get_class($column) || $column == null) {
            continue;
        }
//            pr($attribute);
//            pr($generator->getArrayForeignKeys());
//            die;
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // FIELDS
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ($tableSchema === false) {
            //foreach ($generator->getColumnNames() as $name) {
            echo "            '" . $name . "',\n";
            //}
        } else {
            //foreach ($generator->getTableSchema()->columns as $column) {

            $format = $generator->generateColumnFormat($column);
            if (!empty($generator->campiIndex)) {
                //TODO
            } else {
                if (!(in_array($column->name, ['id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by']))) {
                    if ($column->type === 'date') {
                        echo "            [
                'attribute'=>'$column->name',
                'format'=>['date',(isset(Yii::\$app->modules['datecontrol']['displaySettings']['date'])) ? Yii::\$app->modules['datecontrol']['displaySettings']['date'] : 'd-m-Y'],                
            ],\n";
                    } elseif ($column->type === 'time') {
                        echo "            [
                'attribute'=>'$column->name',
                'format'=>['time',(isset(Yii::\$app->modules['datecontrol']['displaySettings']['time'])) ? Yii::\$app->modules['datecontrol']['displaySettings']['time'] : 'H:i:s A'],               
            ],\n";
                    } elseif ($column->type === 'datetime' || $column->type === 'timestamp') {
                        echo "            [
                'attribute'=>'$column->name',
                'format'=>['datetime',(isset(Yii::\$app->modules['datecontrol']['displaySettings']['datetime'])) ? Yii::\$app->modules['datecontrol']['displaySettings']['datetime'] : 'd-m-Y H:i:s A'],                
            ],\n";
                    } else if ($column->type == 'smallint') {
                        echo "  [
                                'attribute' => '$column->name',
                                'format'=> 'statosino',
                            ],\n";
                    } else {
                        echo "            '" . $column->name . ($format === 'ntext' ? ":html" : "") . "',\n";
                    }
                }
            }
            //}
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
                $firstField = reset($Relation['toFields']);
                if (!(in_array($attributoEntita, $arrayAttributi))) {
                    $arrayAttributi[] = $attributoEntita;
                    $indx[$attributoEntita] = 0;
                    echo '
                \'' . lcfirst(Inflector::id2camel($attributoEntita, '_')) . '\' => [
                \'attribute\' => \'' . lcfirst(Inflector::id2camel($attributoEntita, '_')) . '\',
                \'format\' => \'html\',
                \'label\' => \'' . ucfirst(addslashes($Relation['descriptorField'])) . '\',
                \'value\' => $model->get' . Inflector::id2camel($attributoEntita, '_') . '(),
                ],
                ';
                } else {
                    $indx[$attributoEntita] = $indx[$attributoEntita] + 1;
                    $newIndx = $indx[$attributoEntita] - 1;
                    $newAttributoEntita = $attributoEntita . $newIndx;
                    echo '
                \'' . lcfirst(Inflector::id2camel($newAttributoEntita, '_')) . '\' => [
                \'attribute\' => \'' . lcfirst(Inflector::id2camel($newAttributoEntita, '_')) . '\',
                \'format\' => \'html\',
                \'label\' => \'' . ucfirst(addslashes($Relation['descriptorField'])) . '\',
                \'value\' => $model->get' . Inflector::id2camel($newAttributoEntita, '_') . '(),
                ],
                ';
                }
            }
        }
    }
    ?>
    ],    
    ]) ?>

</div>

<div id="form-actions" class="bk-btnFormContainer pull-right">
    <?= "<?=" ?> Html::a(Yii::t('amoscore', 'Chiudi'), Url::previous(), ['class' => 'btn btn-secondary']); <?= "?>" ?>
</div>
