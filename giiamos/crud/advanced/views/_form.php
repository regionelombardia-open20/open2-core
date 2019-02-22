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
use lispa\amos\core\helpers\Html;
use lispa\amos\core\forms\CreatedUpdatedWidget;

/**
 * @var yii\web\View $this
 * @var \lispa\amos\core\giiamos\crud\Generator $generator
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

echo "<?php\n" .
 "/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    " . StringHelper::dirname(ltrim($generator->viewPath, '\\')) . "/views" . " 
 */";

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

use lispa\amos\core\helpers\Html;
use lispa\amos\core\forms\ActiveForm;
use kartik\datecontrol\DateControl;
use lispa\amos\core\forms\Tabs;
use lispa\amos\core\forms\CloseSaveButtonWidget;
use lispa\amos\core\forms\RequiredFieldsTipWidget;
use yii\helpers\Url;
use lispa\amos\core\forms\editors\Select;
use yii\helpers\ArrayHelper;
use lispa\amos\core\icons\AmosIcons;
use yii\bootstrap\Modal;
use yii\redactor\widgets\Redactor;
use yii\helpers\Inflector;

/**
* @var yii\web\View $this
* @var <?= ltrim($generator->modelClass, '\\') ?> $model
* @var yii\widgets\ActiveForm $form
*/

<?php if (isset($campis['date'])): ?>
    $this->registerJs("
    <?php foreach ($campis['date'] as $dateArr) { ?>
        $('#<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-<?= $dateArr ?>" . ((isset($fid))? $fid : 0) . "').change(function(){
        if($('#<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-<?= $dateArr ?>" . ((isset($fid))? $fid : 0) . "').val() == ''){
        $('#<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-<?= $dateArr ?>" . ((isset($fid))? $fid : 0) . "-disp-kvdate .input-group-addon.kv-date');
        } else {
        if($('#<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-<?= $dateArr ?>" . ((isset($fid))? $fid : 0) . "-disp-kvdate .input-group-addon.kv-date-remove').length == 0){
        $('#<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-<?= $dateArr ?>" . ((isset($fid))? $fid : 0) . "-disp-kvdate').append('<span class=\"input-group-addon kv-date-remove\" title=\"Pulisci campo\"><i class=\"glyphicon glyphicon-remove\"></i></span>');
        initDPRemove('<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-<?= $dateArr ?>" . ((isset($fid))? $fid : 0) . "-disp');
        }
        }
        });
    <?php } ?>
    ", yii\web\View::POS_READY);
<?php endif;
?>

<?= " ?>" ?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form col-xs-12 nop">

    <?= "<?php " ?>
    $form = ActiveForm::begin([
    'options' => [
    'id' => '<?= Inflector::camel2id($classModel) ?>_' . ((isset($fid))? $fid : 0),
    'data-fid' => (isset($fid))? $fid : 0,
    'data-field' => ((isset($dataField))? $dataField : ''),
    'data-entity' => ((isset($dataEntity))? $dataEntity : ''),
    'class' => ((isset($class))? $class : '')
    ]
    ]);
    <?= " ?>" ?>

    <?= "<?php // " ?>$form->errorSummary($model, ['class' => 'alert-danger alert fade in']);<?= " ?>" ?>

    <?php
    foreach ($generator->getFormTabsAsArray() as $tabName) {
        $divClRowElNum = 2;     // numero di elementi in un DIV con classe ROW
        $divClRowCnt = 0;     // contatore per DIV con classe ROW
        $isDandDivClRow = FALSE; // flag se ho un DIV con classe ROW pendente (in attesa di chiusura)
        $isTextArea = FALSE; // flag per TEXTAREA
        $tabNameSanitize = Inflector::slug($tabName);
        ?>

        <?php
        if (!($divClRowCnt % $divClRowElNum)) {
            if ($isDandDivClRow) {
                echo '</div>';
            }
            echo '<div class="row">';
            $isDandDivClRow = TRUE;
        }

        echo '<div class="col-xs-12">';

        $reportModule = \Yii::$app->getModule('report');
        $reportFlagWidget = '';
        if (isset($reportModule) && in_array($model->className(), $reportModule->modelsEnabled)) {
            $reportFlagWidget = \lispa\amos\report\widgets\ReportFlagWidget::widget([
                        'model' => $model,
            ]);
        }

        echo Html::tag('h2', lispa\amos\core\module\BaseAmosModule::t('amoscore', 'Settings') .
                CreatedUpdatedWidget::widget(['model' => $model, 'isTooltip' => true]) .
                $reportFlagWidget, ['class' => 'subtitle-form']);

        echo '<div class="col-md-8 col xs-12">';

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // FIELDS
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        foreach ($generator->getAttributesTab($tabName) as $attribute) {

            $column = $model->getTableSchema()->getColumn($attribute);
            if (!get_class($column) || $column == null) {
                continue;
            }
            if ((in_array($attribute, $generator->getArrayForeignKeys()))) {
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                // RELATIONS
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                $arrayAttributi = [];
                $indx = [];

                $Relation = (array) $generator->getMmRelationsSingle($attribute);

//                if (Inflector::id2camel($Relation['fromEntity'], '_') == $classModel) {
                    $attributoEntita = $Relation['toEntity'];

                    $arrayAttributi[] = $attributoEntita;

                    $firstField = $Relation['toFields'];
                    $indx[$attributoEntita] = 0;

                    if (!(in_array($attributoEntita, $arrayAttributi))) {
                        $divClRowCnt++;

                        echo '<?php 
                        if(\Yii::$app->getUser()->can(\'' . strtoupper(Inflector::id2camel($Relation['toEntity'], '_')) . '_CREATE\')){
                            $append = \' canInsert\';
                        } else {
                            $append = NULL;
                        }
                        ?>';
                        echo '<?= $form->field($model, \'' . $Relation['fromFieldName'] . '\')->widget(Select::classname(), [
                        \'data\' => ArrayHelper::map(\\backend\\modules\\' . $Relation['toModule'] . '\\models\\' . Inflector::id2camel($Relation['toEntity'], '_') . '::find()->asArray()->all(), 
                        \'id\',\'' . $firstField['toFieldName'] . '\'),
                        \'language\' => substr(Yii::$app->language, 0, 2),
                        \'options\' => [\'multiple\' => ' . (($Relation['type'] == 'mtm') ? 'true' : 'false') . ',
                            \'id\' => \'' . Inflector::id2camel($attributoEntita, '_') . '\' . $fid,
                            \'placeholder\' => \'Seleziona ...\',
                            \'class\' => \'dynamicCreation\' . $append,
                            \'data-model\' => \'' . $Relation['toEntity'] . '\',
                            \'data-field\' => \'' . $firstField['toFieldName'] . '\',
                            \'data-module\' => \'' . $Relation['toModule'] . '\',
                            \'data-entity\' => \'' . Inflector::camel2id($Relation['toEntity']) . '\',
                            \'data-toggle\' => \'tooltip\'
                        ],
                        \'pluginOptions\' => [
                            \'allowClear\' => true
                        ],
                        \'pluginEvents\' => [
                            "select2:open" => "dynamicInsertOpening"
                        ]
                        ])->label(\'' . ucfirst(addslashes($Relation['descriptorField'])) . '\') 
                        ?> ';
                    } else {
                        $indx[$attributoEntita] = $indx[$attributoEntita] + 1;
                        $newIndx = $indx[$attributoEntita] - 1;
                        $newAttributoEntita = $attributoEntita . $newIndx;
                        ?>
                        <?php
                        echo '<?php 
                        if(\Yii::$app->getUser()->can(\'' . strtoupper(Inflector::id2camel($Relation['toEntity'], '_')) . '_CREATE\')) {
                            $append = \' canInsert\';
                        } else {
                            $append = NULL;
                        }
                        ?>';
                        echo '
                        <?= $form->field($model, \'' . $Relation['fromFieldName'] . '\')->widget(Select::classname(), [
                        \'data\' => ArrayHelper::map(\\backend\\modules\\' . $Relation['toModule'] . '\\models\\' . Inflector::id2camel($Relation['toEntity'], '_') . '::find()->asArray()->all(),\'id\',\'' . $firstField['toFieldName'] . '\'),
                        \'language\' => substr(Yii::$app->language, 0, 2),
                        \'options\' => [
                            \'id\' => \'' . Inflector::id2camel($newAttributoEntita, '_') . '\' . $fid,
                            \'multiple\' => ' . (($Relation['type'] == 'mtm') ? 'true' : 'false') . ',
                            \'placeholder\' => \'Seleziona ...\',
                            \'class\' => \'dynamicCreation\' . $append,
                            \'data-model\' => \'' . $Relation['toEntity'] . '\',
                            \'data-field\' => \'' . $firstField['toFieldName'] . '\',
                            \'data-module\' => \'' . $Relation['toModule'] . '\',
                            \'data-entity\' => \'' . Inflector::camel2id($Relation['toEntity']) . '\',
                            \'data-toggle\' => \'tooltip\'
                        ],
                        \'pluginOptions\' => [
                            \'allowClear\' => true
                        ],
                        \'pluginEvents\' => [
                            "select2:open" => "dynamicInsertOpening"
                        ]
                        ])->label(\'' . ucfirst(addslashes($Relation['descriptorField'])) . '\') 
                        ?>';
                    }
//                }
            } else {
                if ($column->type == 'text') {
                    $isTextArea = TRUE;
                    $divClRowCnt = 0; // Le TEXTAREA ottengono una riga tutta per loro
                }

                $prepend = $generator->prependActiveField($attribute, $model);
                $field = $generator->activeField($attribute);
                $append = $generator->appendActiveField($attribute, $model);
                if ($prepend) {
                    echo "\n\t\t\t<?php " . $prepend . " ?>";
                }
                echo '<!-- ' . $column->name . ' ' . $column->type . ' -->';
                if ($column->type == 'date') {
                    echo "\n\t\t\t<?= \$form->field(\$model, '" . $column->name . "')->widget(DateControl::classname(), [
                           \t\t\t\t'options' => [
                           \t\t\t\t'id' => lcfirst(Inflector::id2camel(\\yii\\helpers\\StringHelper::basename(\$model->className()), '_')) . '-" . $column->name . "' . ((isset(\$fid))? \$fid : 0),
                           \t\t\t\t'layout' => '{input} {picker} ' . ((\$model->" . $column->name . " == '')? '' : '{remove}')]
                        \t\t\t]); ?>";
                } else if ($column->type == 'text') {
                    echo "\n\t\t\t<?= \$form->field(\$model, '" . $column->name . "')->widget(yii\\redactor\\widgets\\Redactor::className(), [
                            \t\t\t\t'options' => [
                            \t\t\t\t\t'id' => '" . $column->name . "' . \$fid,
                            \t\t\t\t\t],
                            \t\t\t\t'clientOptions' => [
                            \t\t\t\t'language' => substr(Yii::\$app->language, 0, 2),
                            \t\t\t\t'plugins' => ['clips', 'fontcolor', 'imagemanager'],
                            \t\t\t\t'buttons' => ['format', 'bold', 'italic', 'deleted', 'lists', 'image', 'file', 'link', 'horizontalrule'],
                            \t\t\t\t],
                \t\t\t]);
                \t\t\t?>";
                } else if ($column->type == 'smallint') {
                    echo "\n\t\t\t<?= \$form->field(\$model, '" . $column->name . "')->dropDownList([
                           \t\t\t\t0 => 'NO',
                           \t\t\t\t1 => 'SI',                           
                        \t\t\t]); ?>";
                } else if ($field) {
                    echo "\n\t\t\t<?= " . $field . " ?>";
                }
                if ($append) {
                    echo "\n\t\t\t<?php " . $append . " ?>";
                }
            }
        }
        echo "<?= RequiredFieldsTipWidget::widget(); ?>";
        echo "<?= CloseSaveButtonWidget::widget(['model' => \$model]); ?>";

        echo "<?php ActiveForm::end(); ?>";

        echo '</div>';

        echo '<div class="col-md-4 col xs-12">';

        echo '</div>';

        if ($isDandDivClRow) { // Chiude l'eventuale DIV ROW pendente
            echo '</div>';
            $isDandDivClRow = FALSE;
        }

        echo '<div class="clearfix"></div> ';
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ?>


</div>
</div>
