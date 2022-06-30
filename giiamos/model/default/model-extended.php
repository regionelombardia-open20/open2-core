<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\giiamos\model\default
 * @category   CategoryName
 */
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * This is the template for generating the model class of a specified table.
 *
 * @var yii\web\View $this
 * @var yii\gii\generators\model\Generator $generator
 * @var string $tableName full table name
 * @var string $className class name
 * @var yii\db\TableSchema $tableSchema
 * @var string[] $labels list of attribute labels (name => label)
 * @var string[] $rules list of validation rules
 * @var array $relations list of relations (name => relation declaration)
 */
echo "<?php\n";
$campis = [];
$count  = 0;

if (!empty($generator->campiIndex)) {
    //TODO
} else {
    foreach ($tableSchema->columns as $attribute) {
        if (++$count < 9 && !(in_array($attribute,
                ['id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by']))) {
            if ($attribute->type == 'date') {
                $campis['date'][] = $attribute->name;
            }
        }
    }
}


$primoCampo = "";
foreach ($generator->newRules as $colonna => $dati) {
    $primoCampo = $colonna;
}
?>

namespace <?= $generator->ns ?>;

use Yii;
use yii\helpers\ArrayHelper;
<?php if ($interfacessel): ?> 
    <?php foreach ($interfacessel as $i): ?>
        use <?= $i.";\n" ?>
    <?php endforeach; ?>
<?php endif; ?>
/**
* This is the model class for table "<?= $tableName ?>".
*/
class <?= $className ?> extends \<?= $generator->ns ?>\base\<?= $className ?><?php if ($interfacessel): ?> implements 
    <?php
    $numItems = count($interfacessel);
    $index    = 0;
    foreach ($interfacessel as $i):
        ?>
        <?php
        $pathsee = explode('\\', $i);
        $i_name  = array_pop($pathsee);
        ?>
        <?php if (++$index === $numItems): ?> <?= $i_name ?>
        <?php else: ?>
            <?= $i_name.", " ?> <?php endif; ?>

    <?php endforeach; ?>
<?php endif; ?>
{
<?php
foreach ($campis as $fieldType => $fieldNames) {
    foreach ($fieldNames as $fieldName) {
        if ($fieldType == 'date') {
            ?>
            public $<?= $fieldName ?>_from;
            public $<?= $fieldName ?>_to;
            <?php
        }
    }
}
?>
public function representingColumn()
{
return [
//inserire il campo o i campi rappresentativi del modulo
<?php
$ind = 0;
foreach ((array) $representingColumn as $col):
    $ind++;
    ?>
    '<?= $col ?>',
    <?php
endforeach;
if (empty($representingColumn) && strlen($primoCampo)) {
    ?>
    '<?= $primoCampo ?>',
    <?php
}
?>
];
}

public function attributeHints(){
return [
<?php foreach ($generator->newRules as $key => $value) { ?>
    '<?= $key ?>' => \Yii::t('amosapp', '<?= addslashes(isset($value['hints']) ? $value['hints'] : '') ?>'),
<?php } ?>
];
}

/**
* Returns the text hint for the specified attribute.
* @param string $attribute the attribute name
* @return string the attribute hint
*/
public function getAttributeHint($attribute) {
$hints = $this->attributeHints();
return isset($hints[$attribute]) ? $hints[$attribute] : null;
}

public function rules()
{
return ArrayHelper::merge(parent::rules(), [
<?php
$arrayRules = [];
if (count($newRules) > 0) {
    foreach ($newRules as $rule) {
        $arrayRules[$rule['validate']] = 1;
        switch ($rule['validate']) {
            case 'piva_cf_azienda':
                echo "[['".$rule['slug']."'],'checkPartitaIva'],\n
                                      [['".$rule['slug']."'],'string', 'length' => 11],";
                break;
            case 'codice_fiscale_persona':
                echo "[['".$rule['slug']."'],'checkCodiceFiscale'],\n
                                      [['".$rule['slug']."'],'string', 'length' => 16],";
                break;
            case 'date':
                echo "[['".$rule['slug']."'], 'date', 'format' => 'php:Y-m-d'],";
                break;
            case 'email':
                echo "[['".$rule['slug']."'], 'email'],";
                break;
        }
    }
}
?>
]);
}

public function attributeLabels()
{
return
ArrayHelper::merge(
parent::attributeLabels(),
[
<?php
foreach ($campis as $fieldType => $fieldNames) {
    foreach ($fieldNames as $fieldName) {
        if ($fieldType == 'date') {
            ?>
            '<?= $fieldName ?>_from' => 'Da <?= $fieldName ?>',
            '<?= $fieldName ?>_to'   => 'A  <?= $fieldName ?>',
            <?php
        }
    }
}
?>
]);
}

<?php if (isset($arrayRules['codice_fiscale_persona'])): ?>
    /**
    * Verifica la validità del codice fiscale di una persona fisica
    * @param type $attribute
    * @param type $params
    */
    public function checkCodiceFiscale($attribute, $params) {
    $codiceFiscale = $this->$attribute;
    if (!$codiceFiscale) {
    $isValid = true;
    } // se non può essere null se ne deve occupare qualcun altro
    if (strlen($codiceFiscale) != 16) {
    $isValid = false;
    } else {
    $codiceFiscale = strtoupper($codiceFiscale);
    if (!preg_match("/^[A-Z0-9]+$/i", $codiceFiscale)) {
    $isValid = false;
    }
    $s = 0;
    for ($i = 1; $i <= 13; $i += 2) {
    $c = $codiceFiscale[$i];
    if ('0' <= $c && $c <= '9')
    $s += ord($c) - ord('0');
    else
    $s += ord($c) - ord('A');
    }
    for ($i = 0; $i <= 14; $i += 2) {
    $c = $codiceFiscale[$i];
    switch ($c) {
    case '0':
    $s += 1;
    break;
    case '1':
    $s += 0;
    break;
    case '2':
    $s += 5;
    break;
    case '3':
    $s += 7;
    break;
    case '4':
    $s += 9;
    break;
    case '5':
    $s += 13;
    break;
    case '6':
    $s += 15;
    break;
    case '7':
    $s += 17;
    break;
    case '8':
    $s += 19;
    break;
    case '9':
    $s += 21;
    break;
    case 'A':
    $s += 1;
    break;
    case 'B':
    $s += 0;
    break;
    case 'C':
    $s += 5;
    break;
    case 'D':
    $s += 7;
    break;
    case 'E':
    $s += 9;
    break;
    case 'F':
    $s += 13;
    break;
    case 'G':
    $s += 15;
    break;
    case 'H':
    $s += 17;
    break;
    case 'I':
    $s += 19;
    break;
    case 'J':
    $s += 21;
    break;
    case 'K':
    $s += 2;
    break;
    case 'L':
    $s += 4;
    break;
    case 'M':
    $s += 18;
    break;
    case 'N':
    $s += 20;
    break;
    case 'O':
    $s += 11;
    break;
    case 'P':
    $s += 3;
    break;
    case 'Q':
    $s += 6;
    break;
    case 'R':
    $s += 8;
    break;
    case 'S':
    $s += 12;
    break;
    case 'T':
    $s += 14;
    break;
    case 'U':
    $s += 16;
    break;
    case 'V':
    $s += 10;
    break;
    case 'W':
    $s += 22;
    break;
    case 'X':
    $s += 25;
    break;
    case 'Y':
    $s += 24;
    break;
    case 'Z':
    $s += 23;
    break;
    }
    }
    if (isset($codiceFiscale[15])) {

    if (chr($s % 26 + ord('A')) != $codiceFiscale[15]) {
    $isValid = false;
    } else {
    $isValid = true;
    }
    }
    }
    if (!$isValid) {
    $this->addError($attribute, \Yii::t('amoscore', 'The tax code is not in a permitted format'));
    }
    }
    <?php
endif;
if (isset($arrayRules['piva_cf_azienda'])):
    ?>
    /**
    * Verifica la validità del codice fiscale (numerico) o della partita iva
    * @param type $attribute
    * @param type $params
    */
    public function checkPartitaIva($attribute, $params) {
    $partitaIva = $this->$attribute;
    $isValid = false;
    if (!$partitaIva) {
    $isValid = true;
    } else if (strlen($partitaIva) != 11) {
    $isValid = false;
    } else if (strlen($partitaIva) == 11) {
    //la p.iva deve avere solo cifre
    if (!preg_match("/^[0-9]+$/i", $partitaIva)) {
    $isValid = false;
    }
    else {
    $primo = 0;
    for ($i = 0; $i <= 9; $i+=2) {
    $primo+= ord($partitaIva[$i]) - ord('0');
    }

    for ($i = 1; $i <= 9; $i+=2) {
    $secondo = 2 * ( ord($partitaIva[$i]) - ord('0') );

    if ($secondo > 9)
    $secondo = $secondo - 9;
    $primo+=$secondo;
    }
    if ((10 - $primo % 10) % 10 != ord($partitaIva[10]) - ord('0')) {
    $isValid = false;
    } else {
    $isValid = true;
    }
    }
    }
    if (!$isValid) {
    $this->addError($attribute, \Yii::t('amoscore', 'The TAX code/VAT code is not in a permitted format'));
    }
    }
<?php endif; ?>

public static function getEditFields() {
$labels = self::attributeLabels();

return [
<?php
foreach ($tableSchema->columns as $attribute) {
    if (!(in_array($attribute->name,
            ['id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by']))) {
        ?>
        [
        'slug' => '<?= $attribute->name ?>',
        'label' => $labels['<?= $attribute->name ?>'],
        'type' => '<?= $attribute->type ?>'
        ],
        <?php
    }
}
?>
];
}
<?php if ($methodssel): ?> 
    <?php foreach ($methodssel as $key => $value): ?>
        <?php
        $pathsee   = explode('\\', $value);
        $str       = "";
        $reflector = new ReflectionClass($value);

        $fs = file_get_contents($reflector->getFileName());

        //Get the parameters of a method
        $parameters = $reflector->getMethod($key)->getParameters();

        if ($parameters) {
            foreach ($parameters as $p) {
                if ($p->isOptional()) {
                    $pos = strpos($fs, $p->name);
                    $res = null;
                    preg_match_all('/\\((\\$[^\\)]*)*\\)/i', $fs, $res);
                    if (!empty($res[1])) {
                        foreach ($res[1] as $k => $v) {
                            $var1 = explode(',', $v);
                            foreach ($var1 as $k1 => $v1) {
                                $var2 = explode('=', $v1);
                                if (count($var2) == 2) {
                                    $nameAttr = str_replace('$', '', trim($var2[0]));
                                    if ($nameAttr == $p->name) {
                                        $str .= ' $'.$p->name.' = '.trim($var2[1]).',';
                                    }
                                }
                            }
                        }
                    } else {

                    }
                } else {
                    $str .= ' $'.$p->name.',';
                }
            }
        }
        ?>
        /**
        *  @inheritdoc
        */
        public function <?= $key ?>(<?= $str ? substr($str, 0, -1) : '' ?>) {
        //
        }


    <?php endforeach; ?>
<?php endif; ?>

/**
* @return string marker path
*/
public function getIconMarker(){
return null; //TODO
}

/**
* If events are more than one, set 'array' => true in the calendarView in the index.
* @return array events
*/
public function getEvents() {
return NULL; //TODO
}

/**
* @return url event (calendar of activities)
*/
public function getUrlEvent() {
return NULL; //TODO e.g. Yii::$app->urlManager->createUrl([]);
}

/**
* @return color event 
*/
public function getColorEvent() {
return NULL; //TODO
}

/**
* @return title event
*/
public function getTitleEvent() {
return NULL; //TODO
}




}
