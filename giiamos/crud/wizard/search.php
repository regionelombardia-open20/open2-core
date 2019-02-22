<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\giiamos\crud\wizard
 * @category   CategoryName
 */

use yii\helpers\StringHelper;
use yii\helpers\Inflector;

/**
 * This is the template for generating CRUD search class of the specified model.
 *
 * @var yii\web\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */
$modelClass = StringHelper::basename($generator->modelClass);
$table = Inflector::camel2id($modelClass, '_');
$model = new $generator->modelClass;
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $modelAlias = $modelClass . 'Model';
}
$rules = $generator->generateSearchRules();
$labels = $generator->generateSearchLabels();
$searchAttributes = $generator->getSearchAttributes();
$searchConditions = $generator->generateSearchConditions();

$campis = [];
$tuttiCampi = [];
foreach ($generator->getFormTabsAsArray() as $nomeTab) {
    foreach ($generator->getAttributesTab($nomeTab) as $attribute) {
        $tuttiCampi[] = $attribute;
        $colonna = $model->getTableSchema()->getColumn($attribute);
        if ($colonna->type == 'date') {
            $campis['date'][] = $colonna->name;
        }
    }
}

echo "<?php\n";


//Inizio gestione relazioni
$arrayAttributi = [];
$indx = [];
$condizioniAggiuntive = [];
$join = [];
$otherRules = [];
$order = [];
$ordinamenti = [];
foreach ((array) $generator->mmRelations as $Relation):
    if (Inflector::id2camel($Relation['fromEntity'], '_') == $modelClass):
        $attributoEntita = $Relation['toEntity'];

        if (!(in_array($attributoEntita, $arrayAttributi))):
            $arrayAttributi[] = $attributoEntita;
            $indx[$attributoEntita] = 0;

            $join[] = "\$query->joinWith('" . lcfirst(Inflector::id2camel(lcfirst($attributoEntita), '_')) . "');";

            $fields = [];
            foreach ($Relation['toFields'] as $field) {
                $fields[] = $field['toFieldName'];
            }
            if (count($fields) == 1) {
                $newExpr = "new \yii\db\Expression('" . $attributoEntita . "." . $fields[0] . "')";

                if (!in_array($attributoEntita, $ordinamenti)) {
                    $order[] = "'" . lcfirst(Inflector::id2camel(lcfirst($attributoEntita), '_')) . "' => [
                    'asc' => ['" . $attributoEntita . "." . $fields[0] . "' => SORT_ASC],
                    'desc' => ['" . $attributoEntita . "." . $fields[0] . "' => SORT_DESC],
                ],";
                    $ordinamenti[] = $attributoEntita;
                }
            } else if (count($fields) > 1) {
                $idx = 0;

                $newExpr = "new \yii\db\Expression('concat_ws(\" \",";
                foreach ($fields as $field) {
                    $newExpr .= (($idx == 0) ? '' : ', ') . $attributoEntita . '.' . $field;
                    $idx++;
                }
                $newExpr .= ")')";
                if (!in_array($attributoEntita, $ordinamenti)) {
                    $textOrder = "'" . lcfirst(Inflector::id2camel(lcfirst($attributoEntita), '_')) . "' => [
                    'asc' => ['concat_ws(\" \",";
                    foreach ($fields as $field) {
                        $textOrder .= $attributoEntita . "." . $field . ",";
                    }
                    $textOrder = substr($textOrder, 0, strlen($textOrder) - 1);
                    $textOrder .= ")' => SORT_ASC],
                     'desc' => ['concat_ws(\" \",";
                    foreach ($fields as $field) {
                        $textOrder .= $attributoEntita . "." . $field . ",";
                    }
                    $textOrder = substr($textOrder, 0, strlen($textOrder) - 1);
                    $textOrder .= ")' => SORT_DESC],
                     ],";
                    $order[] = $textOrder;
                    $ordinamenti[] = $attributoEntita;
                }
            }
            $otherRules[] = "['attr" . Inflector::id2camel($attributoEntita, '_') . "Mm', 'safe']";
            $condizioniAggiuntive[] = "\$query->andFilterWhere(['like', $newExpr, \$this->attr" . Inflector::id2camel($attributoEntita, '_') . "Mm])";

        else:
            $indx[$attributoEntita] = $indx[$attributoEntita] + 1;
            $newIndx = $indx[$attributoEntita] - 1;
            $newAttributoEntita = $attributoEntita . $newIndx;

            $fields = [];
            foreach ($Relation['toFields'] as $field) {
                $fields[] = $field['toFieldName'];
            }
            if (count($fields) == 1) {
                $newExpr = "new \yii\db\Expression('" . $attributoEntita . "." . $fields[0] . "')";
                if (!in_array($attributoEntita, $ordinamenti)) {
                    $order[] = "'" . lcfirst(Inflector::id2camel(lcfirst($attributoEntita), '_')) . "' => [
                    'asc' => ['" . $attributoEntita . "." . $fields[0] . "' => SORT_ASC],
                    'desc' => ['" . $attributoEntita . "." . $fields[0] . "' => SORT_DESC],
                ],";
                    $ordinamenti[] = $attributoEntita;
                }
            } else if (count($fields) > 1) {
                $idx = 0;

                $newExpr = "new \yii\db\Expression('concat_ws(\" \",";
                foreach ($fields as $field) {
                    $newExpr .= (($idx == 0) ? '' : ', ') . $attributoEntita . '.' . $field;
                    $idx++;
                }
                $newExpr .= ")')";
                if (!in_array($attributoEntita, $ordinamenti)) {
                    $textOrder = "'" . lcfirst(Inflector::id2camel(lcfirst($attributoEntita), '_')) . "' => [
                    'asc' => ['concat_ws(\" \",";
                    foreach ($fields as $field) {
                        $textOrder .= $attributoEntita . "." . $field . ",";
                    }
                    $textOrder = substr($textOrder, 0, strlen($textOrder) - 1);
                    $textOrder .= ")' => SORT_ASC],
                     'desc' => ['concat_ws(\" \",";
                    foreach ($fields as $field) {
                        $textOrder .= $attributoEntita . "." . $field . ",";
                    }
                    $textOrder = substr($textOrder, 0, strlen($textOrder) - 1);
                    $textOrder .= ")' => SORT_DESC],
                     ],";
                    $order[] = $textOrder;
                    $ordinamenti[] = $attributoEntita;
                }
            }
            $otherRules[] = "['attr" . Inflector::id2camel($newAttributoEntita, '_') . "Mm', 'safe']";
            $condizioniAggiuntive[] = "\$query->andFilterWhere(['like', $newExpr, \$this->attr" . Inflector::id2camel($newAttributoEntita, '_') . "Mm])";
        endif;
    endif;
endforeach;
//fine gestione relazioni
?>

namespace <?= StringHelper::dirname(ltrim($generator->searchModelClass, '\\')) ?>;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use <?= ltrim($generator->modelClass, '\\') . (isset($modelAlias) ? " as $modelAlias" : "") ?>;

/**
* <?= $searchModelClass ?> represents the model behind the search form about `<?= $generator->modelClass ?>`.
*/
class <?= $searchModelClass ?> extends <?= isset($modelAlias) ? $modelAlias : $modelClass ?>

{
public function rules()
{
return [
<?= implode(",\n            ", $rules) ?>,
<?php
foreach ($campis as $fieldType => $fieldNames) {
    ?>
    [[
    <?php
    foreach ($fieldNames as $fieldName) {
        if ($fieldType == 'date') {
            ?>
            '<?= $fieldName ?>_from',
            '<?= $fieldName ?>_to',
            <?php
        }
    }
    ?>
    ], 'safe'],    
    <?php
}
foreach ($otherRules as $AttrRule) {
    ?>
    <?= $AttrRule ?>,
    <?php
}
?>
];
}

public function scenarios()
{
// bypass scenarios() implementation in the parent class
return Model::scenarios();
}

public function search($params)
{
$query = <?= isset($modelAlias) ? $modelAlias : $modelClass ?>::find();

$dataProvider = new ActiveDataProvider([
'query' => $query,
]);

<?php foreach ($join as $Join) { ?>
    <?= $Join ?>
<?php } ?>


$dataProvider->setSort([
'attributes' => [
<?php foreach ($tuttiCampi as $campo) { ?>
    '<?= $campo ?>' => [
    'asc' => ['<?= $table ?>.<?= $campo ?>' => SORT_ASC],
    'desc' => ['<?= $table ?>.<?= $campo ?>' => SORT_DESC],
    ],
<?php } ?>
<?php foreach ($order as $Order) { ?>
    <?= $Order ?>
<?php } ?>
]]);

if (!($this->load($params) && $this->validate())) {
return $dataProvider;
}



<?= implode("\n        ", $searchConditions) ?>
<?php
foreach ($campis as $fieldType => $fieldNames) {
    foreach ($fieldNames as $fieldName) {
        if ($fieldType == 'date') {
            ?>
            $query->andFilterWhere(['>=','<?= $fieldName ?>', $this-><?= $fieldName ?>_from]);
            $query->andFilterWhere(['<=','<?= $fieldName ?>', $this-><?= $fieldName ?>_to]);
            <?php
        }
    }
}

foreach ($condizioniAggiuntive as $newLike) {
    ?>
    <?= $newLike ?>;
    <?php
}
?>

return $dataProvider;
}
}
