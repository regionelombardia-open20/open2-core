<?php
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\giiamos\model
 * @category   CategoryName
 */

namespace lispa\amos\core\giiamos\model;

use Yii;
use yii\gii\CodeFile;
use yii\helpers\Inflector;
use ReflectionClass;
use ReflectionException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Schema;
use yii\db\TableSchema;
use yii\base\NotSupportedException;

class Generator extends \lispa\amos\core\giiamos\Generator
//\yii\gii\generators\model\Generator
{
    const RELATIONS_NONE        = 'none';
    const RELATIONS_ALL         = 'all';
    const RELATIONS_ALL_INVERSE = 'all-inverse';

    public $db                                 = 'db';
    public $ns                                 = 'app\models';
    public $tableName;  
    public $baseClass                          = 'yii\db\ActiveRecord';
    public $generateRelations                  = self::RELATIONS_ALL;
    public $generateRelationsFromCurrentSchema = true;
    public $generateLabelsFromComments         = false;
    public $useTablePrefix                     = false;
    public $useSchemaName                      = true;
    public $generateQuery                      = false;
    public $queryNs                            = 'app\models';
    public $queryClass;
    public $queryBaseClass                     = 'yii\db\ActiveQuery';

    /**
     * @var array fully qualified interfaces name
     */
    public $interfacessel = [];

    /**
     * 
     * @var array key-value pairs for mapping method of interfaces selected
     */
    public $methodssel = [];

    /**
     * @var array key-value pairs. 
     */
    public $baseClassNames = [];

    /**
     * @var string baseclass selected
     */
    public $baseClassName;

    /**
     * @var array key-value pairs. 
     */
    public $baseInterfaceNames = [];

    /**
     * @var array baseInterfaces selected
     */
    public $baseInterfaceNames_sel;

    /**
     * @var array key-value pairs. 
     */
    public $baseclassDynamic = [];
    public $baseActiveRecord = 'yii\db\ActiveRecord';

    /**
     * @var bool whether to overwrite (extended) model classes, will be always created, if file does not exist
     */
    public $generateModelClass = true;
    public $standardbaseClass  = null;

    /**
     * @var null string for the table prefix, which is ignored in generated class name
     */
    public $tablePrefix = null;

    /**
     * @var array key-value pairs for mapping a table-name to class-name, eg. 'prefix_FOObar' => 'FooBar'
     */
    public $tableNameMap = [];
    protected $classNames2;

    /**
     * @var array for new rules
     */
    public $newRules = [];

    /**
     * @var array per relazioni aggiuntive
     */
    public $otherRelations = [];

    /**
     *
     * @var array per le colonne rappresentative
     */
    public $representingColumn;

    /**
     *
     */
    public $pluginName;
    public $migrationName;

    public function init()
    {
        parent::init();
        $this->baseInterfaceNames     = GeneratoConfig::getDefinition()['baseInterfaceNames'];
        $this->baseInterfaceNames_sel = GeneratoConfig::getDefinition()['baseInterfaceNames'];
        $this->baseClassNames         = GeneratoConfig::getDefinition()['baseClassNames'];

        foreach (GeneratoConfig::getDefinition()['baseClassNames'] as $key => $value) {
            $index                                  = 0;
            $baseclass_sel                          = $key.'_sel';
            $this->baseclassDynamic[$baseclass_sel] = [];
            foreach (GeneratoConfig::getDefinition()[$key] as $keybaseclass => $valuebaseclass) {
                $this->baseclassDynamic[$baseclass_sel][$index] = $keybaseclass;
                $index++;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Amos Model';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates an ActiveRecord class and base class for the specified database table.';
    }

    public function checkIsArray()
    {
        if (!is_array($this->baseInterfaceNames)) {
            $this->addError('baseInterfaceNames', 'baseInterfaceNames is not array!');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(),
            [
            [['generateModelClass'], 'boolean'],
            [['tablePrefix', 'migrationName', 'baseClassName', 'baseInterfaceNames_sel', 'baseclassDynamic'], 'safe'],
            [$this->dynamicRules(), 'safe'],
            [['db', 'ns', 'tableName', 'modelClass', 'baseClass', 'queryNs', 'queryClass', 'queryBaseClass'], 'filter', 'filter' => 'trim'],
            [['ns', 'queryNs'], 'filter', 'filter' => function ($value) {
                    return trim($value, '\\');
                }],
            [['db', 'ns', 'tableName', 'baseClass', 'queryNs', 'queryBaseClass'], 'required'],
            [['db', 'modelClass', 'queryClass'], 'match', 'pattern' => '/^\w+$/', 'message' => 'Only word characters are allowed.'],
            [['ns', 'baseClass', 'queryNs', 'queryBaseClass'], 'match', 'pattern' => '/^[\w\\\\]+$/', 'message' => 'Only word characters and backslashes are allowed.'],
            [['tableName'], 'match', 'pattern' => '/^([\w ]+\.)?([\w\* ]+)$/', 'message' => 'Only word characters, and optionally spaces, an asterisk and/or a dot are allowed.'],
            [['db'], 'validateDb'],
            [['ns', 'queryNs'], 'validateNamespace'],
            [['tableName'], 'validateTableName'],
            [['modelClass'], 'validateModelClass', 'skipOnEmpty' => false],
            [['baseClass'], 'validateClass', 'params' => ['extends' => ActiveRecord::className()]],
            [['queryBaseClass'], 'validateClass', 'params' => ['extends' => ActiveQuery::className()]],
            [['generateRelations'], 'in', 'range' => [self::RELATIONS_NONE, self::RELATIONS_ALL, self::RELATIONS_ALL_INVERSE]],
            [['generateLabelsFromComments', 'useTablePrefix', 'useSchemaName', 'generateQuery', 'generateRelationsFromCurrentSchema'],
                'boolean'],
            [['enableI18N'], 'boolean'],
            [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],
        ]);
    }

    private function dynamicRules()
    {
        $arraysel = [];
        $index    = 0;
        foreach (GeneratoConfig::getDefinition()['baseClassNames'] as $key => $value) {
            $baseclass_sel      = $key.'_sel';
            $arraysel[$index++] = 'baseclassDynamic['.$baseclass_sel.']';
        }
        return $arraysel;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(
            parent::attributeLabels(),
            [
            'ns' => 'Namespace',
            'db' => 'Database Connection ID',
            'tableName' => 'Table Name',
            'modelClass' => 'Model Class Name',
            'baseClass' => 'Base Class',
            'generateRelations' => 'Generate Relations',
            'generateRelationsFromCurrentSchema' => 'Generate Relations from Current Schema',
            'generateLabelsFromComments' => 'Generate Labels from DB Comments',
            'generateQuery' => 'Generate ActiveQuery',
            'queryNs' => 'ActiveQuery Namespace',
            'queryClass' => 'ActiveQuery Class',
            'queryBaseClass' => 'ActiveQuery Base Class',
            'useSchemaName' => 'Use Schema Name',
            'baseClassNames' => 'Base Class',
            'baseInterfaceNames_sel' => 'Interfaces',
            'baseclassDynamic' => 'Interfaces',
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return array_merge(
            parent::hints(),
            [
            'ns' => 'This is the namespace of the ActiveRecord class to be generated, e.g., <code>app\models</code>',
            'db' => 'This is the ID of the DB application component.',
            'tableName' => 'This is the name of the DB table that the new ActiveRecord class is associated with, e.g. <code>post</code>.
                The table name may consist of the DB schema part if needed, e.g. <code>public.post</code>.
                The table name may end with asterisk to match multiple table names, e.g. <code>tbl_*</code>
                will match tables who name starts with <code>tbl_</code>. In this case, multiple ActiveRecord classes
                will be generated, one for each matching table name; and the class names will be generated from
                the matching characters. For example, table <code>tbl_post</code> will generate <code>Post</code>
                class.',
            'modelClass' => 'This is the name of the ActiveRecord class to be generated. The class name should not contain
                the namespace part as it is specified in "Namespace". You do not need to specify the class name
                if "Table Name" ends with asterisk, in which case multiple ActiveRecord classes will be generated.',
            'baseClass' => 'This is the base class of the new ActiveRecord class. It should be a fully qualified namespaced class name.',
            'generateRelations' => 'This indicates whether the generator should generate relations based on
                foreign key constraints it detects in the database. Note that if your database contains too many tables,
                you may want to uncheck this option to accelerate the code generation process.',
            'generateRelationsFromCurrentSchema' => 'This indicates whether the generator should generate relations from current schema or from all available schemas.',
            'generateLabelsFromComments' => 'This indicates whether the generator should generate attribute labels
                by using the comments of the corresponding DB columns.',
            'useTablePrefix' => 'This indicates whether the table name returned by the generated ActiveRecord class
                should consider the <code>tablePrefix</code> setting of the DB connection. For example, if the
                table name is <code>tbl_post</code> and <code>tablePrefix=tbl_</code>, the ActiveRecord class
                will return the table name as <code>{{%post}}</code>.',
            'useSchemaName' => 'This indicates whether to include the schema name in the ActiveRecord class
                when it\'s auto generated. Only non default schema would be used.',
            'generateQuery' => 'This indicates whether to generate ActiveQuery for the ActiveRecord class.',
            'queryNs' => 'This is the namespace of the ActiveQuery class to be generated, e.g., <code>app\models</code>',
            'queryClass' => 'This is the name of the ActiveQuery class to be generated. The class name should not contain
                the namespace part as it is specified in "ActiveQuery Namespace". You do not need to specify the class name
                if "Table Name" ends with asterisk, in which case multiple ActiveQuery classes will be generated.',
            'queryBaseClass' => 'This is the base class of the new ActiveQuery class. It should be a fully qualified namespaced class name.',
            'generateModelClass' => 'This indicates whether the generator should generate the model class, this should usually be done only once. The model-base class is always generated.',
            'tablePrefix' => 'Custom table prefix, eg <code>app_</code>.<br/><b>Note!</b> overrides <code>yii\db\Connection</code> prefix!',
            'baseClassNames' => 'This is the base class of the new ActiveRecord class. It should be a fully qualified namespaced class name.',]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function autoCompleteData()
    {
        $db = $this->getDbConnection();
        if ($db !== null) {
            return [
                'tableName' => function () use ($db) {
                    return $db->getSchema()->getTableNames();
                },
            ];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['model.php', 'model-extended.php'];
    }

    /**
     * {@inheritdoc}
     */
    public function stickyAttributes()
    {
        return array_merge(parent::stickyAttributes(),
            ['baseInterfaceNames', 'ns', 'db', 'baseClass', 'generateRelations', 'generateLabelsFromComments', 'queryNs',
            'queryBaseClass', 'useTablePrefix', 'generateQuery']);
    }

    /**
     * Returns the `tablePrefix` property of the DB connection as specified
     *
     * @return string
     * @since 2.0.5
     */
    public function getTablePrefix()
    {
        $db = $this->getDbConnection();
        if ($db !== null) {
            return $db->tablePrefix;
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        try {

            $files     = [];
            $relations = $this->generateRelations();
            $db        = $this->getDbConnection();

            foreach ($this->getTableNames() as $tableName) {
                $className   = $this->generateClassName($tableName);
                $tableSchema = $db->getTableSchema($tableName);

                $this->getInterfaceAndMethods();

                $params = [
                    'baseClassName' => $this->baseClassName,
                    'interfacessel' => $this->interfacessel,
                    'methodssel' => $this->methodssel,
                    'tableName' => $tableName,
                    'className' => $className,
                    'tableSchema' => $tableSchema,
                    'labels' => $this->generateLabels($tableSchema),
                    'rules' => $this->generateRules($tableSchema),
                    'relations' => isset($relations[$tableName]) ? $relations[$tableName] : [],
                    'otherRelations' => $this->otherRelations,
                    'newRules' => $this->newRules,
                    'ns' => $this->ns,
                    'representingColumn' => $this->representingColumn,
                    'pluginName' => $this->pluginName
                ];

                $newNs  = null;
                $newNsM = null;
                if (!empty($this->vendorPath)) {
                    $newNs  = $this->createPathFromNamespace();
                    $newNsM = $this->createPathFromNamespace(true);
                }
                $files[] = new CodeFile(
                    (!empty($newNs) ? $newNs : Yii::getAlias('@'.str_replace('\\', '/', $this->ns))).'/base/'.$className.'.php',
                    $this->render('model.php', $params)
                );

                $modelClassFile = (!empty($newNs) ? $newNs : Yii::getAlias('@'.str_replace('\\', '/', $this->ns))).'/'.$className.'.php';

                if ($this->generateModelClass || !is_file($modelClassFile)) {
                    $files[] = new CodeFile(
                        $modelClassFile, $this->render('model-extended.php', $params)
                    );
                }

                //file name of the migration
                if (array_key_exists('preview', $_POST)) {
                    $this->migrationName = 'm'.date('ymd_His').'_'.$tableName.'_permissions';
                }

                $ns_migration_model = preg_replace('%[^//]models.*$%', '\migrations',
                    (!empty($newNsM) ? $newNsM : $this->ns));

                $migrationClassFile = (!empty($newNsM) ? $ns_migration_model : Yii::getAlias('@'.str_replace('\\', '/',
                            $ns_migration_model))).'/'.$this->migrationName;

                $files[] = new CodeFile(
                    $migrationClassFile.'.php', $this->render('WidgetMigrationAuthItem.php', $params)
                );
            }

            $this->doUnset();
            return $files;
        } catch (\Exception $e) {
            //pr($e->getFile() . ' ' . $e->getLine());die;
            return NULL;
        }
    }

    /**
     * Generates the properties for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated properties (property => type)
     * @since 2.0.6
     */
    protected function generateProperties($table)
    {
        $properties = [];
        foreach ($table->columns as $column) {
            $columnPhpType = $column->phpType;
            if ($columnPhpType === 'integer') {
                $type = 'int';
            } elseif ($columnPhpType === 'boolean') {
                $type = 'bool';
            } else {
                $type = $columnPhpType;
            }
            $properties[$column->name] = [
                'type' => $type,
                'name' => $column->name,
                'comment' => $column->comment,
            ];
        }

        return $properties;
    }

    /**
     * Generates the attribute labels for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated attribute labels (name => label)
     */
    public function generateLabels($table)
    {
        $labels = [];
        foreach ($table->columns as $column) {
            if ($this->generateLabelsFromComments && !empty($column->comment)) {
                $labels[$column->name] = $column->comment;
            } elseif (!strcasecmp($column->name, 'id')) {
                $labels[$column->name] = 'ID';
            } else {
                $label = Inflector::camel2words($column->name);
                if (!empty($label) && substr_compare($label, ' id', -3, 3, true) === 0) {
                    $label = substr($label, 0, -3).' ID';
                }
                $labels[$column->name] = $label;
            }
        }

        return $labels;
    }

    /**
     * Generates validation rules for the specified table.
     * @param \yii\db\TableSchema $table the table schema
     * @return array the generated validation rules
     */
    public function generateRules($table)
    {
        $types   = [];
        $lengths = [];
        foreach ($table->columns as $column) {
            if ($column->autoIncrement) {
                continue;
            }
            if (!$column->allowNull && $column->defaultValue === null) {
                $types['required'][] = $column->name;
            }
            switch ($column->type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_TINYINT:
                    $types['integer'][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][]  = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                case Schema::TYPE_JSON:
                    $types['safe'][]    = $column->name;
                    break;
                default: // strings
                    if ($column->size > 0) {
                        $lengths[$column->size][] = $column->name;
                    } else {
                        $types['string'][] = $column->name;
                    }
            }
        }
        $rules      = [];
        $driverName = $this->getDbDriverName();
        foreach ($types as $type => $columns) {
            if ($driverName === 'pgsql' && $type === 'integer') {
                $rules[] = "[['".implode("', '", $columns)."'], 'default', 'value' => null]";
            }
            $rules[] = "[['".implode("', '", $columns)."'], '$type']";
        }
        foreach ($lengths as $length => $columns) {
            $rules[] = "[['".implode("', '", $columns)."'], 'string', 'max' => $length]";
        }

        $db = $this->getDbConnection();

        // Unique indexes rules
        try {
            $uniqueIndexes = array_merge($db->getSchema()->findUniqueIndexes($table), [$table->primaryKey]);
            $uniqueIndexes = array_unique($uniqueIndexes, SORT_REGULAR);
            foreach ($uniqueIndexes as $uniqueColumns) {
                // Avoid validating auto incremental columns
                if (!$this->isColumnAutoIncremental($table, $uniqueColumns)) {
                    $attributesCount = count($uniqueColumns);

                    if ($attributesCount === 1) {
                        $rules[] = "[['".$uniqueColumns[0]."'], 'unique']";
                    } elseif ($attributesCount > 1) {
                        $columnsList = implode("', '", $uniqueColumns);
                        $rules[]     = "[['$columnsList'], 'unique', 'targetAttribute' => ['$columnsList']]";
                    }
                }
            }
        } catch (NotSupportedException $e) {
            // doesn't support unique indexes information...do nothing
        }

        // Exist rules for foreign keys
        foreach ($table->foreignKeys as $refs) {
            $refTable       = $refs[0];
            $refTableSchema = $db->getTableSchema($refTable);
            if ($refTableSchema === null) {
                // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                continue;
            }
            $refClassName     = $this->generateClassName($refTable);
            unset($refs[0]);
            $attributes       = implode("', '", array_keys($refs));
            $targetAttributes = [];
            foreach ($refs as $key => $value) {
                $targetAttributes[] = "'$key' => '$value'";
            }
            $targetAttributes = implode(', ', $targetAttributes);
            $rules[]          = "[['$attributes'], 'exist', 'skipOnError' => true, 'targetClass' => $refClassName::className(), 'targetAttribute' => [$targetAttributes]]";
        }

        return $rules;
    }

    /**
     * Generates relations using a junction table by adding an extra viaTable().
     * @param \yii\db\TableSchema the table being checked
     * @param array $fks obtained from the checkJunctionTable() method
     * @param array $relations
     * @return array modified $relations
     */
    private function generateManyManyRelations($table, $fks, $relations)
    {
        $db = $this->getDbConnection();

        foreach ($fks as $pair) {
            list($firstKey, $secondKey) = $pair;
            $table0       = $firstKey[0];
            $table1       = $secondKey[0];
            unset($firstKey[0], $secondKey[0]);
            $className0   = $this->generateClassName($table0);
            $className1   = $this->generateClassName($table1);
            $table0Schema = $db->getTableSchema($table0);
            $table1Schema = $db->getTableSchema($table1);

            if ($table0Schema === null || $table1Schema === null) {
                continue;
            }

            $link                                              = $this->generateRelationLink(array_flip($secondKey));
            $viaLink                                           = $this->generateRelationLink($firstKey);
            $relationName                                      = $this->generateRelationName($relations, $table0Schema,
                key($secondKey), true);
            $relations[$table0Schema->fullName][$relationName] = [
                "return \$this->hasMany($className1::className(), $link)->viaTable('"
                .$this->generateTableName($table->name)."', $viaLink);",
                $className1,
                true,
            ];

            $link                                              = $this->generateRelationLink(array_flip($firstKey));
            $viaLink                                           = $this->generateRelationLink($secondKey);
            $relationName                                      = $this->generateRelationName($relations, $table1Schema,
                key($firstKey), true);
            $relations[$table1Schema->fullName][$relationName] = [
                "return \$this->hasMany($className0::className(), $link)->viaTable('"
                .$this->generateTableName($table->name)."', $viaLink);",
                $className0,
                true,
            ];
        }

        return $relations;
    }

    /**
     * @return string[] all db schema names or an array with a single empty string
     * @throws NotSupportedException
     * @since 2.0.5
     */
    protected function getSchemaNames()
    {
        $db = $this->getDbConnection();

        if ($this->generateRelationsFromCurrentSchema) {
            if ($db->schema->defaultSchema !== null) {
                return [$db->schema->defaultSchema];
            }
            return [''];
        }

        $schema = $db->getSchema();
        if ($schema->hasMethod('getSchemaNames')) { // keep BC to Yii versions < 2.0.4
            try {
                $schemaNames = $schema->getSchemaNames();
            } catch (NotSupportedException $e) {
                // schema names are not supported by schema
            }
        }
        if (!isset($schemaNames)) {
            if (($pos = strpos($this->tableName, '.')) !== false) {
                $schemaNames = [substr($this->tableName, 0, $pos)];
            } else {
                $schemaNames = [''];
            }
        }
        return $schemaNames;
    }

    /**
     * Adds inverse relations
     *
     * @param array $relations relation declarations
     * @return array relation declarations extended with inverse relation names
     * @since 2.0.5
     */
    protected function addInverseRelations($relations)
    {
        $db            = $this->getDbConnection();
        $relationNames = [];

        $schemaNames = $this->getSchemaNames();
        foreach ($schemaNames as $schemaName) {
            foreach ($db->schema->getTableSchemas($schemaName) as $table) {
                $className = $this->generateClassName($table->fullName);
                foreach ($table->foreignKeys as $refs) {
                    $refTable       = $refs[0];
                    $refTableSchema = $db->getTableSchema($refTable);
                    if ($refTableSchema === null) {
                        // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                        continue;
                    }
                    unset($refs[0]);
                    $fks = array_keys($refs);

                    $leftRelationName                                             = $this->generateRelationName($relationNames,
                        $table, $fks[0], false);
                    $relationNames[$table->fullName][$leftRelationName]           = true;
                    $hasMany                                                      = $this->isHasManyRelation($table,
                        $fks);
                    $rightRelationName                                            = $this->generateRelationName(
                        $relationNames, $refTableSchema, $className, $hasMany
                    );
                    $relationNames[$refTableSchema->fullName][$rightRelationName] = true;

                    $relations[$table->fullName][$leftRelationName][0]           = rtrim($relations[$table->fullName][$leftRelationName][0],
                            ';')
                        ."->inverseOf('".lcfirst($rightRelationName)."');";
                    $relations[$refTableSchema->fullName][$rightRelationName][0] = rtrim($relations[$refTableSchema->fullName][$rightRelationName][0],
                            ';')
                        ."->inverseOf('".lcfirst($leftRelationName)."');";
                }
            }
        }
        return $relations;
    }

    /**
     * Determines if relation is of has many type
     *
     * @param TableSchema $table
     * @param array $fks
     * @return bool
     * @since 2.0.5
     */
    protected function isHasManyRelation($table, $fks)
    {
        $uniqueKeys = [$table->primaryKey];
        try {
            $uniqueKeys = array_merge($uniqueKeys, $this->getDbConnection()->getSchema()->findUniqueIndexes($table));
        } catch (NotSupportedException $e) {
            // ignore
        }
        foreach ($uniqueKeys as $uniqueKey) {
            if (count(array_diff(array_merge($uniqueKey, $fks), array_intersect($uniqueKey, $fks))) === 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * Generates the link parameter to be used in generating the relation declaration.
     * @param array $refs reference constraint
     * @return string the generated link parameter.
     */
    protected function generateRelationLink($refs)
    {
        $pairs = [];
        foreach ($refs as $a => $b) {
            $pairs[] = "'$a' => '$b'";
        }

        return '['.implode(', ', $pairs).']';
    }

    /**
     * Checks if the given table is a junction table, that is it has at least one pair of unique foreign keys.
     * @param \yii\db\TableSchema the table being checked
     * @return array|bool all unique foreign key pairs if the table is a junction table,
     * or false if the table is not a junction table.
     */
    protected function checkJunctionTable($table)
    {
        if (count($table->foreignKeys) < 2) {
            return false;
        }
        $uniqueKeys = [$table->primaryKey];
        try {
            $uniqueKeys = array_merge($uniqueKeys, $this->getDbConnection()->getSchema()->findUniqueIndexes($table));
        } catch (NotSupportedException $e) {
            // ignore
        }
        $result           = [];
        // find all foreign key pairs that have all columns in an unique constraint
        $foreignKeys      = array_values($table->foreignKeys);
        $foreignKeysCount = count($foreignKeys);

        for ($i = 0; $i < $foreignKeysCount; $i++) {
            $firstColumns = $foreignKeys[$i];
            unset($firstColumns[0]);

            for ($j = $i + 1; $j < $foreignKeysCount; $j++) {
                $secondColumns = $foreignKeys[$j];
                unset($secondColumns[0]);

                $fks = array_merge(array_keys($firstColumns), array_keys($secondColumns));
                foreach ($uniqueKeys as $uniqueKey) {
                    if (count(array_diff(array_merge($uniqueKey, $fks), array_intersect($uniqueKey, $fks))) === 0) {
                        // save the foreign key pair
                        $result[] = [$foreignKeys[$i], $foreignKeys[$j]];
                        break;
                    }
                }
            }
        }
        return empty($result) ? false : $result;
    }

    /**
     * @return array the generated relation declarations
     */
    protected function generatePreRelations()
    {
        if ($this->generateRelations === self::RELATIONS_NONE) {
            return [];
        }

        $db          = $this->getDbConnection();
        $relations   = [];
        $schemaNames = $this->getSchemaNames();
        foreach ($schemaNames as $schemaName) {
            foreach ($db->getSchema()->getTableSchemas($schemaName) as $table) {
                $className = $this->generateClassName($table->fullName);
                foreach ($table->foreignKeys as $refs) {
                    $refTable       = $refs[0];
                    $refTableSchema = $db->getTableSchema($refTable);
                    if ($refTableSchema === null) {
                        // Foreign key could point to non-existing table: https://github.com/yiisoft/yii2-gii/issues/34
                        continue;
                    }
                    unset($refs[0]);
                    $fks          = array_keys($refs);
                    $refClassName = $this->generateClassName($refTable);

                    // Add relation for this table
                    $link                                       = $this->generateRelationLink(array_flip($refs));
                    $relationName                               = $this->generateRelationName($relations, $table,
                        $fks[0], false);
                    $relations[$table->fullName][$relationName] = [
                        "return \$this->hasOne($refClassName::className(), $link);",
                        $refClassName,
                        false,
                    ];

                    // Add relation for the referenced table
                    $hasMany                                             = $this->isHasManyRelation($table, $fks);
                    $link                                                = $this->generateRelationLink($refs);
                    $relationName                                        = $this->generateRelationName($relations,
                        $refTableSchema, $className, $hasMany);
                    $relations[$refTableSchema->fullName][$relationName] = [
                        "return \$this->".($hasMany ? 'hasMany' : 'hasOne')."($className::className(), $link);",
                        $className,
                        $hasMany,
                    ];
                }

                if (($junctionFks = $this->checkJunctionTable($table)) === false) {
                    continue;
                }

                $relations = $this->generateManyManyRelations($table, $junctionFks, $relations);
            }
        }

        if ($this->generateRelations === self::RELATIONS_ALL_INVERSE) {
            return $this->addInverseRelations($relations);
        }

        return $relations;
    }

    /**
     * Generates a class name from the specified table name.
     *
     * @param string $tableName the table name (which may contain schema prefix)
     *
     * @return string the generated class name
     */
    protected function generateClassName($tableName, $useSchemaName = NULL)
    {

        #Yii::trace("Generating class name for '{$tableName}'...", __METHOD__);
        if (isset($this->classNames2[$tableName])) {
            #Yii::trace("Using '{$this->classNames2[$tableName]}' for '{$tableName}' from classNames2.", __METHOD__);
            return $this->classNames2[$tableName];
        }

        if (isset($this->tableNameMap[$tableName])) {
            Yii::trace("Converted '{$tableName}' from tableNameMap.", __METHOD__);
            return $this->classNames2[$tableName] = $this->tableNameMap[$tableName];
        }

        if (($pos = strrpos($tableName, '.')) !== false) {
            $tableName = substr($tableName, $pos + 1);
        }

        $db         = $this->getDbConnection();
        $patterns   = [];
        $patterns[] = "/^{$this->tablePrefix}(.*?)$/";
        $patterns[] = "/^(.*?){$this->tablePrefix}$/";
        $patterns[] = "/^{$db->tablePrefix}(.*?)$/";
        $patterns[] = "/^(.*?){$db->tablePrefix}$/";

        if (strpos($this->tableName, '*') !== false) {
            $pattern = $this->tableName;
            if (($pos     = strrpos($pattern, '.')) !== false) {
                $pattern = substr($pattern, $pos + 1);
            }
            $patterns[] = '/^'.str_replace('*', '(\w+)', $pattern).'$/';
        }

        $className = $tableName;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $tableName, $matches)) {
                $className = $matches[1];
                Yii::trace("Mapping '{$tableName}' to '{$className}' from pattern '{$pattern}'.", __METHOD__);
                break;
            }
        }

        $returnName                    = Inflector::id2camel($className, '_');
        Yii::trace("Converted '{$tableName}' to '{$returnName}'.", __METHOD__);
        return $this->classNames2[$tableName] = $returnName;
    }

    protected function generateRelations()
    {
        try {
            $relations = $this->generatePreRelations();

            // inject namespace
            $ns = "\\{$this->ns}\\";
            foreach ($relations AS $model => $relInfo) {
                foreach ($relInfo AS $relName => $relData) {

                    $relations[$model][$relName][0] = preg_replace(
                        '/(has[A-Za-z0-9]+\()([a-zA-Z0-9]+::)/', '$1__NS__$2', $relations[$model][$relName][0]
                    );
                    $relations[$model][$relName][0] = str_replace('__NS__', $ns, $relations[$model][$relName][0]);
                }
            }
            return $relations;
        } catch (\Exception $e) {
            return NULL;
        }
    }

    protected function generateMethodsForWrapper($baseClass)
    {

        $clsabstract = new ReflectionClass($baseclssel);
        foreach ($clsabstract->getMethods() as $m) {
            try {
                if ($m->class !== $baseclssel) {
                    if (strpos($m->class, 'interface') !== false) {
                        $methodtoimplement[$m->name] = $m->class;
                    }
                }

                if ($methodtoimplement) {
                    foreach ($methodtoimplement as $key => $value) {

                        $pathsee    = explode('\\', $value);
                        $str        = "";
                        $reflector  = new ReflectionClass($value);
                        //Get the parameters of a method
                        $parameters = $reflector->getMethod($key)->getParameters();
                        if ($parameters) {
                            foreach ($parameters as $p) {
                                $str .= ' $'.$p->name.',';
                            }
                        }

                        $strmethods .= ' /**
                                    *  @inheritdoc
                                    */
                                    public function '.$key.'( '.$str ? substr($str, 0, -1) : ''.') {
                                    //
                                    } ';
                    }
                }
            } catch (ReflectionException $e) {
                Yii::getLogger()->log($e->getMessage(), \yii\log\Logger::LEVEL_ERROR);
            }
        }
        return $strmethods;
    }

    /**
     * Generate a relation name for the specified table and a base name.
     * @param array $relations the relations being generated currently.
     * @param \yii\db\TableSchema $table the table schema
     * @param string $key a base name that the relation name may be generated from
     * @param bool $multiple whether this is a has-many relation
     * @return string the relation name
     */
    protected function generateRelationName($relations, $table, $key, $multiple)
    {

//        pr('$baseClassWrapper'); die;
        static $baseModel;
        /* @var $baseModel \yii\db\ActiveRecord */
        if ($baseModel === null) {
            $baseClass          = $this->baseClass;
            $baseClassReflector = new \ReflectionClass($baseClass);
            if ($baseClassReflector->isAbstract()) {
                //Extra check for security to validate that $baseClass is indeed a class since this variable is used in eval
                if (!class_exists($baseClass)) {
                    throw new InvalidConfigException("Class '$class' does not exist or has syntax error.");
                }

                $baseClassWrapper = 'namespace '.__NAMESPACE__.';'.
                    'class GiiBaseClassWrapper extends \\'.$baseClass.' {'.
                    'public static function tableName(){'.
                    'return "'.addslashes($table->fullName).'";'.
                    '}'.$this->generateMethodsForWrapper($baseClass).
                    '};'.
                    'return new GiiBaseClassWrapper();';

                $baseModel = eval($baseClassWrapper);
            } else {
                $baseModel = new $baseClass();
            }
            $baseModel->setAttributes([]);
        }
        if (!empty($key) && strcasecmp($key, 'id')) {
            if (substr_compare($key, 'id', -2, 2, true) === 0) {
                $key = rtrim(substr($key, 0, -2), '_');
            } elseif (substr_compare($key, 'id', 0, 2, true) === 0) {
                $key = ltrim(substr($key, 2, strlen($key)), '_');
            }
        }
        if ($multiple) {
            $key = Inflector::pluralize($key);
        }
        $name    = $rawName = Inflector::id2camel($key, '_');
        $i       = 0;
        while ($baseModel->hasProperty(lcfirst($name))) {
            $name = $rawName.($i++);
        }
        while (isset($table->columns[lcfirst($name)])) {
            $name = $rawName.($i++);
        }
        while (isset($relations[$table->fullName][$name])) {
            $name = $rawName.($i++);
        }

        return $name;
    }

    /**
     * Validates the [[modelClass]] attribute.
     */
    public function validateModelClass()
    {
        if ($this->isReservedKeyword($this->modelClass)) {
            $this->addError('modelClass', 'Class name cannot be a reserved PHP keyword.');
        }
        if ((empty($this->tableName) || substr_compare($this->tableName, '*', -1, 1)) && $this->modelClass == '') {
            $this->addError('modelClass', 'Model Class cannot be blank if table name does not end with asterisk.');
        }
    }

    /**
     * Validates the [[db]] attribute.
     */
    public function validateDb()
    {
        if (!Yii::$app->has($this->db)) {
            $this->addError('db', 'There is no application component named "db".');
        } elseif (!Yii::$app->get($this->db) instanceof Connection) {
            $this->addError('db', 'The "db" application component must be a DB connection instance.');
        }
    }

    /**
     * Validates the [[tableName]] attribute.
     */
    public function validateTableName()
    {
        if (strpos($this->tableName, '*') !== false && substr_compare($this->tableName, '*', -1, 1)) {
            $this->addError('tableName', 'Asterisk is only allowed as the last character.');

            return;
        }
        $tables = $this->getTableNames();
        if (empty($tables)) {
            $this->addError('tableName', "Table '{$this->tableName}' does not exist.");
        } else {
            foreach ($tables as $table) {
                $class = $this->generateClassName($table);
                if ($this->isReservedKeyword($class)) {
                    $this->addError('tableName', "Table '$table' will generate a class which is a reserved PHP keyword.");
                    break;
                }
            }
        }
    }
    protected $tableNames;
    protected $classNames;

    /**
     * @return array the table names that match the pattern specified by [[tableName]].
     */
    protected function getTableNames()
    {
        if ($this->tableNames !== null) {
            return $this->tableNames;
        }
        $db = $this->getDbConnection();
        if ($db === null) {
            return [];
        }
        $tableNames = [];
        if (strpos($this->tableName, '*') !== false) {
            if (($pos = strrpos($this->tableName, '.')) !== false) {
                $schema  = substr($this->tableName, 0, $pos);
                $pattern = '/^'.str_replace('*', '\w+', substr($this->tableName, $pos + 1)).'$/';
            } else {
                $schema  = '';
                $pattern = '/^'.str_replace('*', '\w+', $this->tableName).'$/';
            }

            foreach ($db->schema->getTableNames($schema) as $table) {
                if (preg_match($pattern, $table)) {
                    $tableNames[] = $schema === '' ? $table : ($schema.'.'.$table);
                }
            }
        } elseif (($table = $db->getTableSchema($this->tableName, true)) !== null) {
            $tableNames[]                       = $this->tableName;
            $this->classNames[$this->tableName] = $this->modelClass;
        }

        return $this->tableNames = $tableNames;
    }

    /**
     * Generates the table name by considering table prefix.
     * If [[useTablePrefix]] is false, the table name will be returned without change.
     * @param string $tableName the table name (which may contain schema prefix)
     * @return string the generated table name
     */
    public function generateTableName($tableName)
    {
        if (!$this->useTablePrefix) {
            return $tableName;
        }

        $db = $this->getDbConnection();
        if (preg_match("/^{$db->tablePrefix}(.*?)$/", $tableName, $matches)) {
            $tableName = '{{%'.$matches[1].'}}';
        } elseif (preg_match("/^(.*?){$db->tablePrefix}$/", $tableName, $matches)) {
            $tableName = '{{'.$matches[1].'%}}';
        }
        return $tableName;
    }

    /**
     * Return array populated with all the methods 
     * - key method name
     * - value fully qualified interfaces name
     * 
     * [
     *  [isCommentable] => lispa\amos\comments\models\CommentInterface,
     * ]
     * 
     * @param type $baseclssel
     * @return type
     */
    protected function getMethods($baseclssel)
    {

        $cls = new ReflectionClass($baseclssel);
        foreach ($cls->getMethods() as $m) {
            try {
                if (!$cls->isInterface()) {
                    if ($m->class !== $baseclssel) {
                        if (strpos($m->class, 'lispa\amos') === 0 && strpos($m->class, 'interface') !== false) {
                            $this->methodssel[$m->name] = $m->class;
                        }
                    }
                } else {
                    $this->methodssel[$m->name] = $m->class;
                }
            } catch (ReflectionException $e) {
                Yii::getLogger()->log($e->getMessage(), \yii\log\Logger::LEVEL_ERROR);
            }
        }
        return $this->methodssel;
    }

    /**
     * Populated $this->methodssel array with all the methods of all the interfaces implemented by the baseclass and
     * all the methods of all the interfaces select.
     * 
     * Populated $this->interfacessel array with all the interfaces select
     */
    protected function getInterfaceAndMethods()
    {

        if (isset($_POST['Generator']['baseClassName']) && !empty($_POST['Generator']['baseClassName'])) {

            if (isset($_POST['Generator']['baseclassDynamic'][$this->baseClassName.'_sel'])) {
                $baseclassname = $this->baseClassName;
                $iselect       = $_POST['Generator']['baseclassDynamic'][$this->baseClassName.'_sel'];
                $n             = 0;
                foreach ($iselect as $i) {
                    if (!isset(GeneratoConfig::getDefinition()[$baseclassname][$i])) {
                        $this->interfacessel[$n++] = $this->baseInterfaceNames[$i];
                        $this->getMethods($this->baseInterfaceNames[$i]);
                    }
                }
            }
        } else {
            $iselect = $_POST['Generator']['baseInterfaceNames_sel'];
            $n       = 0;
            if (!empty($iselect)) {
                foreach ($iselect as $i) {
                    $this->interfacessel[$n++] = $this->baseInterfaceNames[$i];
                    $this->getMethods($this->baseInterfaceNames[$i]);
                }
            }
        }
        $this->getMethods($this->baseClass);
    }

    private function doUnset()
    {
        foreach ($this->baseClassNames as $key => $value) {
            unset($_POST['Generator']['baseclassDynamic'][$key.'_sel']);
        }
        unset($_POST['Generator']['baseInterfaceNames_sel']);
        unset($_POST['Generator']['baseClass']);
        $_POST['Generator']['baseClass'] = $this->baseActiveRecord;
    }  

    /**
     * Generates a query class name from the specified model class name.
     * @param string $modelClassName model class name
     * @return string generated class name
     */
    protected function generateQueryClassName($modelClassName)
    {
        $queryClassName = $this->queryClass;
        if (empty($queryClassName) || strpos($this->tableName, '*') !== false) {
            $queryClassName = $modelClassName.'Query';
        }
        return $queryClassName;
    }

    /**
     * @return Connection the DB connection as specified by [[db]].
     */
    protected function getDbConnection()
    {
        return Yii::$app->get($this->db, false);
    }

    /**
     * @return string|null driver name of db connection.
     * In case db is not instance of \yii\db\Connection null will be returned.
     * @since 2.0.6
     */
    protected function getDbDriverName()
    {
        /** @var Connection $db */
        $db = $this->getDbConnection();
        return $db instanceof \yii\db\Connection ? $db->driverName : null;
    }

    /**
     * Checks if any of the specified columns is auto incremental.
     * @param \yii\db\TableSchema $table the table schema
     * @param array $columns columns to check for autoIncrement property
     * @return bool whether any of the specified columns is auto incremental.
     */
    protected function isColumnAutoIncremental($table, $columns)
    {
        foreach ($columns as $column) {
            if (isset($table->columns[$column]) && $table->columns[$column]->autoIncrement) {
                return true;
            }
        }

        return false;
    }
}