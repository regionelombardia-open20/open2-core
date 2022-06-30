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
$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();
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
$research = TRUE;
if (($tableSchema = $generator->getTableSchema()) !== false) {
    foreach ($generator->getColumnNames() as $name) {
        if (!(in_array($name, ['id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by']))) {
            $research = TRUE;
            continue;
        }
    }
}
//pr($generator);die;
?>

use open20\amos\core\helpers\Html;
use <?= $generator->indexWidgetType === 'grid' ? "open20\\amos\\core\\views\\DataProviderView" : "yii\\widgets\\ListView" ?>;
use yii\widgets\Pjax;

/**
* @var yii\web\View $this
* @var yii\data\ActiveDataProvider $dataProvider
<?= !empty($generator->searchModelClass) ? " * @var " . ltrim($generator->searchModelClass, '\\') . " \$model\n" : '' ?>
*/

$this->title = <?= $generator->generateString(Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>;
$this->params['breadcrumbs'][] = ['label' => '<?= ucfirst($generator->descriptiveNameModule) ?>', 'url' => ['/<?= StringHelper::basename($generator->moduleNs) ?>']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">
    <?php if (!empty($generator->searchModelClass)): ?>
        <?= "    <?= " . (($research === TRUE) ? " " : "// ") ?> $this->render('_search', ['model' => $model, 'originAction' => Yii::$app->controller->action->id ]); ?>
    <?php endif; ?>

    <?php if ($generator->indexWidgetType === 'grid'): ?>
        <?= "<?= " ?>DataProviderView::widget([
        'dataProvider' => $dataProvider,
        <?= !empty($generator->searchModelClass) ? "//'filterModel' => \$model,\n   'currentView' => \$currentView,\n   'gridView' => [\n   'columns' => [\n" : "'currentView' => \$currentView,\n   'gridView' => [\n   'columns' => [\n"; ?>
        ['class' => 'yii\grid\SerialColumn'],

        <?php
//        foreach ($generator->ordinalFields as $ordinalField) {
        foreach ($generator->getColumnNames() as $attribute) {
            $column = $generator->getTableSchema()->getColumn($attribute);
//            pr($column);die;
            if (!get_class($column) || $column == null) {
                continue;
            }
//            pr($attribute);
//            pr($generator->getArrayForeignKeys());
//            die;
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // FIELDS
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $count = 0;
            $tableSchema = $generator->getTableSchema();
            if (!empty($generator->campiIndex)) {
                //TODO
            } else {
                if ($tableSchema === false) {
                    //foreach ($generator->getColumnNames() as $name) {
                    //if (++$count < 6 && !(in_array($name, ['id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by']))) {
                    if (!(in_array($attribute, ['id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by']))) {
                        echo "            '" . $attribute . "',\n";
                    } else {
                        echo "            // '" . $attribute . "',\n";
                    }
                    //}
                } else {
                    //foreach ($tableSchema->columns as $column) {
                    if (!(in_array($column->name, ['id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by']))) {
                        $format = $generator->generateColumnFormat($column);
                        if ($column->type === 'date') {
                            $columnDisplay = "            '" . $column->name . ":date',";
                        } elseif ($column->type === 'time') {
                            $columnDisplay = "            ['attribute'=>'$column->name','format'=>['time',(isset(Yii::\$app->modules['datecontrol']['displaySettings']['time'])) ? Yii::\$app->modules['datecontrol']['displaySettings']['time'] : 'H:i:s A']],";
                        } elseif ($column->type === 'datetime' || $column->type === 'timestamp') {
                            $columnDisplay = "            ['attribute'=>'$column->name','format'=>['datetime',(isset(Yii::\$app->modules['datecontrol']['displaySettings']['datetime'])) ? Yii::\$app->modules['datecontrol']['displaySettings']['datetime'] : 'd-m-Y H:i:s A']],";
                        } else if ($column->type == 'smallint') {
                            $columnDisplay = "            ['attribute'=>'$column->name','format'=> 'statosino'],";
                        } else {
                            $columnDisplay = "            '" . $column->name . ($format === 'ntext' ? ":striptags" : "") . "',";
                        }
                        //if (++$count < 6 && !(in_array($column->name, ['id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by']))) {
                        if (!(in_array($column->name, ['id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by']))) {
                            echo $columnDisplay . "\n";
                        } else {
                            echo "//" . $columnDisplay . " \n";
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
                        \'label\' => \'' . addslashes(ucfirst($Relation['descriptorField'])) . '\',
                        \'value\' => function($model){
                        return strip_tags($model->' . lcfirst(Inflector::id2camel($attributoEntita, '_')) . ');
                        }
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
                        \'label\' => \'' . addslashes(ucfirst($Relation['descriptorField'])) . '\',
                        \'value\' => function($model){
                        return strip_tags($model->' . lcfirst(Inflector::id2camel($attributoEntita, '_')) . ');
                        }
                        ],
                    ';
                        }
                    }
                }
            }
        }
        ?>
        [
        'class' => 'open20\amos\core\views\grid\ActionColumn',
        ],
        ],
        ],
        /*'listView' => [
        'itemView' => '_item',
        'masonry' => FALSE,

        // Se masonry settato a TRUE decommentare e settare i parametri seguenti 
        // nel CSS settare i seguenti parametri necessari al funzionamento tipo
        // .grid-sizer, .grid-item {width: 50&;}
        // Per i dettagli recarsi sul sito http://masonry.desandro.com                                     

        //'masonrySelector' => '.grid',
        //'masonryOptions' => [
        //    'itemSelector' => '.grid-item',
        //    'columnWidth' => '.grid-sizer',
        //    'percentPosition' => 'true',
        //    'gutter' => '20'
        //]
        ],
        'iconView' => [
        'itemView' => '_icon'
        ],
        'mapView' => [
        'itemView' => '_map',          
        'markerConfig' => [
        'lat' => 'domicilio_lat',
        'lng' => 'domicilio_lon',
        'icon' => 'iconMarker',
        ]
        ],
        'calendarView' => [
        'itemView' => '_calendar',
        'clientOptions' => [
        //'lang'=> 'de'
        ],
        'eventConfig' => [
        //'title' => 'titleEvent',
        //'start' => 'data_inizio',
        //'end' => 'data_fine',
        //'color' => 'colorEvent',
        //'url' => 'urlEvent'
        ],
        'array' => false,//se ci sono più eventi legati al singolo record
        //'getEventi' => 'getEvents'//funzione da abilitare e implementare nel model per creare un array di eventi legati al record
        ]*/
        ]); ?>
    <?php else: ?>
        <?= "<?= " ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
        return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
        },
        ]) ?>
    <?php endif; ?>

</div>
