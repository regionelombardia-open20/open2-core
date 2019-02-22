<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\giiamos\crud
 * @category   CategoryName
 */

namespace lispa\amos\core\giiamos\crud;

use Yii;
use yii\db\ActiveRecord;
use yii\gii\CodeFile;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\db\BaseActiveRecord;
use yii\db\Schema;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use schmunk42\giiant\helpers\SaveForm;
use schmunk42\giiant\generators\model\Generator as ModelGenerator;


class Generator extends \lispa\amos\core\giiamos\Generator {
//\schmunk42\giiant\generators\crud\Generator {

    /**
     * @todo review
     *
     * @var string
     */
    public $actionButtonClass = 'yii\grid\ActionColumn';

    /**
     * @var array relations to be excluded in UI rendering
     */
    public $skipRelations = [];

    /**
     * @var string default view path
     */
    public $viewPath = '@backend/views';

    /**
     * @var string table prefix to be removed from class names when auto-detecting model names, eg. `app_` converts table `app_foo` into `Foo`
     */
    public $tablePrefix = null;

    /**
     * @var string prefix for controller route, eg. when generating controllers into subfolders
     */
    public $pathPrefix = null;

    /**
     * @var string Bootstrap CSS-class for form-layout
     */
    public $formLayout = 'horizontal';

    /**
     * @var string translation catalogue for model related translations
     */
    public $modelMessageCategory = 'models';

    /**
     * @var int maximum number of columns to show in grid
     */
    public $gridMaxColumns = 8;

    /**
     * @var int maximum number of columns to show in grid
     */
    public $gridRelationMaxColumns = 8;

    /**
     * @var array array of composer packages (only to show information to the developer in the web UI)
     */
    public $requires = [];

    /**
     * @var bool whether to convert controller name to singular
     */
    public $singularEntities = false;

    /**
     * @var bool whether to add an access filter to controllers
     */
    public $accessFilter = false;
    public $generateAccessFilterMigrations = false;
    public $baseTraits;

    /**
     * @var sting controller base namespace
     */
    public $controllerNs;

    /**
     * @var bool whether to overwrite extended controller classes
     */
    public $overwriteControllerClass = true;

    /**
     * @var bool whether to overwrite rest/api controller classes
     */
    public $overwriteRestControllerClass = false;

    /**
     * @var bool whether to overwrite search classes
     */
    public $overwriteSearchModelClass = false;

    /**
     * @var bool whether to use phptidy on renderer files before saving
     */
    public $tidyOutput = false;

    /**
     * @var string command-line options for phptidy command
     */
    public $tidyOptions = '';

    /**
     * @var bool whether to use php-cs-fixer to generate PSR compatible output
     */
    public $fixOutput = false;

    /**
     * @var string command-line options for php-cs-fixer command
     */
    public $fixOptions = '';

    /**
     * @var string form field for selecting and loading saved gii forms
     */
    public $savedForm;
    public $moduleNs;
    public $migrationClass;
    public $indexGridClass = 'yii\\grid\\GridView';
    private $_p = [];
   
    public $controllerClass;
    public $indexWidgetType = 'grid';
    public $searchModelClass = '';

    /**
     * @var bool whether to wrap the `GridView` or `ListView` widget with the `yii\widgets\Pjax` widget
     * @since 2.0.5
     */
    public $enablePjax = false;
    public $formTabs;
    public $templates = ['default' => '@vendor/lispa/amos-core/giiamos/crud/default',
        'wizard' => '@vendor/lispa/amos-core/giiamos/crud/wizard',
        'advanced' => '@vendor/lispa/amos-core/giiamos/crud/advanced'];
    public $template = 'advanced';
    public $formTabsSeparator = '|';
    public $formTabsFieldSeparator = ',';
    public $tabsFieldList = [];
    public $providerList = 'lispa\amos\core\giiamos\crud\providers\CallbackProvider,
                            lispa\amos\core\giiamos\crud\providers\DateTimeProvider,
                            lispa\amos\core\giiamos\crud\providers\DateProvider,
                            lispa\amos\core\giiamos\crud\providers\EditorProvider,
                            lispa\amos\core\giiamos\crud\providers\EditableProvider,
                            lispa\amos\core\giiamos\crud\providers\OptsProvider,
                            lispa\amos\core\giiamos\crud\providers\RelationProvider';

    /**
     * @var array Array delle relazioni M2M
     */
    public $mmRelations = [];

    /**
     * @var array Array dei campi da visualizzare nella index 
     */
    public $campiIndex = [];

    /**
     * Descriptive name of the module
     * @var string
     */
    public $descriptiveNameModule;

    /**
     * Ordinals of the fields/relations in the wizard
     * @var array
     */
    public $ordinalFields = [];

    /**
     * @var array key-value pairs. 
     */
    public $relFiledsDynamic = [];
    public $moduleName;
    public $descriptorField;
    public $moduleRelRequired;
    public $arrayForeignKeys = [];
    public $baseControllerClass = 'lispa\amos\core\controllers\CrudController';

    /**
     * @var string translation catalogue
     */
    public $messageCategory = 'amoscore';

    /**
     * @inheritdoc
     */
    public function init() {
        $this->messageCategory = 'amoscore';
        parent::init();

        $this->getArrayForeignKeys();
        $this->dynamicrelFileds();
        $this->providerList = self::getCoreProviders();
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return 'Amos CRUD Generator';
    }

    /**
     * @inheritdoc
     */
    public function getDescription() {
        return 'Questo generatore permette di creare CRUD (Create, Read, Update, Delete) su model specifici';
    }

    /**
     * {@inheritdoc}
     */
    public function successMessage() {
        $return = 'The code has been generated successfully. Please require the following packages with composer:';
        $return .= '<br/><code>' . implode('<br/>', $this->requires) . '</code>';

        return $return;
    }

    /**
     * @inheritdoc
     * 
     */
    public function rules() {
        $arrayRules = [];
        $arrayRules = array_merge(parent::rules(), [
            [
                [
                    'providerList',
                    'actionButtonClass',
                    'viewPath',
                    'pathPrefix',
                    'savedForm',
                    'formLayout',
                    'accessFilter',
                    'generateAccessFilterMigrations',
                    'singularEntities',
                    'modelMessageCategory',
                ],
                'safe',
            ],
            [['viewPath'], 'required'],
            [['controllerClass', 'modelClass', 'searchModelClass', 'baseControllerClass'], 'filter', 'filter' => 'trim'],
            [['modelClass', 'controllerClass', 'baseControllerClass', 'indexWidgetType'], 'required'],
            [['searchModelClass'], 'compare', 'compareAttribute' => 'modelClass', 'operator' => '!==', 'message' => 'Search Model Class must not be equal to Model Class.'],
            [['modelClass', 'controllerClass', 'baseControllerClass', 'searchModelClass'], 'match', 'pattern' => '/^[\w\\\\]*$/',
                'message' => 'Only word characters and backslashes are allowed.'],
            [['modelClass'], 'validateClass', 'params' => ['extends' => BaseActiveRecord::className()]],
            [['baseControllerClass'], 'validateClass', 'params' => ['extends' => Controller::className()]],
            [['controllerClass'], 'match', 'pattern' => '/Controller$/', 'message' => 'Controller class name must be suffixed with "Controller".'],
            [['controllerClass'], 'match', 'pattern' => '/(^|\\\\)[A-Z][^\\\\]+Controller$/', 'message' => 'Controller class name must start with an uppercase letter.'],
            [['controllerClass', 'searchModelClass'], 'validateNewClass'],
            [['indexWidgetType'], 'in', 'range' => ['grid', 'list']],
            [['modelClass'], 'validateModelClass'],
            [['enableI18N', 'enablePjax'], 'boolean'],
            [['messageCategory'], 'validateMessageCategory', 'skipOnEmpty' => false],
            ['viewPath', 'safe'],
            [['formTabs'], 'filter', 'filter' => 'trim'],
            ['tabsFieldList', 'checkIsArray'],
//            [['moduleName', 'descriptorField', 'moduleRelRequired'], 'safe'],
            ['relFiledsDynamic', 'safe'],
            [$this->dynamicRules(), 'safe']
        ]);
        return $arrayRules;
    }

    private function dynamicRules() {
        $arraysel = [];
        $index = 0;
        foreach ($this->getRelationColumnNames() as $key => $value) {
            $arraysel[$index++] = 'relFiledsDynamic[' . $key . '][fields], '
                    . 'relFiledsDynamic[' . $key . '][moduleName], '
                    . 'relFiledsDynamic[' . $key . '][descriptorField], '
                    . 'relFiledsDynamic[' . $key . '][moduleRelRequired], ';
        }
//        pr($arraysel);die;
        return $arraysel;
    }

    private function dynamicrelFileds() {
        foreach ($this->getRelationColumnNames() as $key => $value) {


            $this->relFiledsDynamic[$key] = ['fields', 'moduleName', 'descriptorField', 'moduleRelRequired'];
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return array_merge(parent::attributeLabels(), [
            'modelClass' => 'Model Class',
            'controllerClass' => 'Controller Class',
            'viewPath' => 'View Path',
            'baseControllerClass' => 'Base Controller Class',
            'indexWidgetType' => 'Widget Used in Index Page',
            'searchModelClass' => 'Search Model Class',
            'enablePjax' => 'Enable Pjax',
            'formTabs' => 'Tabs',
            'relFiledsDynamic' => '',
        ]);
    }

    public function checkIsArray() {
        if (!is_array($this->tabsFieldList)) {
            $this->addError('tabsFieldList', 'tabsFieldList is not array!');
        }
    }

    /**
     * @inheritdoc
     */
    public function hints() {
        return array_merge(parent::hints(), [
            'modelClass' => 'This is the ActiveRecord class associated with the table that CRUD will be built upon.
                You should provide a fully qualified class name, e.g., <code>app\models\Post</code>.',
            'controllerClass' => 'This is the name of the controller class to be generated. You should
                provide a fully qualified namespaced class (e.g. <code>app\controllers\PostController</code>),
                and class name should be in CamelCase with an uppercase first letter. Make sure the class
                is using the same namespace as specified by your application\'s controllerNamespace property.',
            'viewPath' => 'Specify the directory for storing the view scripts for the controller. You may use path alias here, e.g.,
                <code>/var/www/basic/controllers/views/post</code>, <code>@app/views/post</code>. If not set, it will default
                to <code>@app/views/ControllerID</code>',
            'baseControllerClass' => 'This is the class that the new CRUD controller class will extend from.
                You should provide a fully qualified class name, e.g., <code>yii\web\Controller</code>.',
            'indexWidgetType' => 'This is the widget type to be used in the index page to display list of the models.
                You may choose either <code>GridView</code> or <code>ListView</code>',
            'searchModelClass' => 'This is the name of the search model class to be generated. You should provide a fully
                qualified namespaced class name, e.g., <code>app\models\PostSearch</code>.',
            'enablePjax' => 'This indicates whether the generator should wrap the <code>GridView</code> or <code>ListView</code>
                widget on the index page with <code>yii\widgets\Pjax</code> widget. Set this to <code>true</code> if you want to get
                sorting, filtering and pagination without page refreshing.',
            'formTabs' => 'Elenco delle tab da creare sulla form <code>tab1|tab2|...</code>.',
            'providerList' => 'Choose the providers to be used.',
            'pathPrefix' => 'Customized route/subfolder for controllers and views eg. <code>crud/</code>. <b>Note!</b> Should correspond to <code>viewPath</code>.',
            'modelMessageCategory' => 'Model message categry.',
                ], SaveForm::hint());
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates() {
        return ['controller.php'];
    }

    /**
     * @inheritdoc
     */
    public function stickyAttributes() {
        return array_merge(parent::stickyAttributes(), ['baseControllerClass', 'indexWidgetType', 'providerList', 'actionButtonClass', 'viewPath', 'pathPrefix']);
    }

    /**
     * Checks if model class is valid
     */
    public function validateModelClass() {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pk = $class::primaryKey();
        if (empty($pk)) {
            $this->addError('modelClass', "The table associated with $class must have primary key(s).");
        }
    }

    /**
     * all form fields for saving in saved forms.
     *
     * @return array
     */
    public function formAttributes() {
        return [
            'modelClass',
            'searchModelClass',
            'controllerClass',
            'baseControllerClass',
            'viewPath',
            'pathPrefix',
            'enableI18N',
            'singularEntities',
            'indexWidgetType',
            'formLayout',
            'actionButtonClass',
            'providerList',
            'template',
            'accessFilter',
            'singularEntities',
            'modelMessageCategory',
        ];
    }

    /**
     * Generates parameter tags for phpdoc
     * @return array parameter tags for phpdoc
     */
    public function generateActionParamComments() {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        $pks = $class::primaryKey();
        if (($table = $this->getTableSchema()) === false) {
            $params = [];
            foreach ($pks as $pk) {
                $params[] = '@param ' . (substr(strtolower($pk), -2) == 'id' ? 'integer' : 'string') . ' $' . $pk;
            }

            return $params;
        }
        if (count($pks) === 1) {
            return ['@param ' . $table->columns[$pks[0]]->phpType . ' $id'];
        } else {
            $params = [];
            foreach ($pks as $pk) {
                $params[] = '@param ' . $table->columns[$pk]->phpType . ' $' . $pk;
            }

            return $params;
        }
    }

    /**
     * Returns table schema for current model class or false if it is not an active record
     * @return boolean|\yii\db\TableSchema
     */
    public function getTableSchema() {
        /** @var ActiveRecord $class */
        $class = $this->modelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema();
        } else {
            return false;
        }
    }

    /**
     * @return array model column names
     */
    public function getFormTabsAsArray() {
        $formTabsAsArray = [];
        if ($this->formTabs) {
            $formTabsAsArray = explode($this->formTabsSeparator, $this->formTabs);
        }

        if (!count($formTabsAsArray)) {
            $formTabsAsArray = ['dettagli'];
        }

        return $formTabsAsArray;
    }

    /**
     * @return array model column names
     */
    public function getAttributesTab($tabCode) {

        $attributes = [];
        if ($this->tabsFieldList && array_key_exists($tabCode, $this->tabsFieldList)) {
            if ($this->tabsFieldList[$tabCode]) {

                $attributes = explode($this->formTabsFieldSeparator, $this->tabsFieldList[$tabCode]);
            }
        } else {
            $attributes = $this->safeAttributes();
        }
        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function preGenerate() {
        $accessDefinitions = require $this->getTemplatePath() . '/access_definition.php';

        $this->controllerNs = \yii\helpers\StringHelper::dirname(ltrim($this->controllerClass, '\\'));
        $this->moduleNs = \yii\helpers\StringHelper::dirname(ltrim($this->controllerNs, '\\'));
        $controllerName = substr(\yii\helpers\StringHelper::basename($this->controllerClass), 0, -10);

        if ($this->singularEntities) {
            $this->modelClass = Inflector::singularize($this->modelClass);
            $this->controllerClass = Inflector::singularize(
                            substr($this->controllerClass, 0, strlen($this->controllerClass) - 10)
                    ) . 'Controller';
            $this->searchModelClass = Inflector::singularize($this->searchModelClass);
        }
        $newControllerFile = $this->createPathFromNs($this->controllerClass);

        if(!empty($newControllerFile)){
            $baseControllerClass = StringHelper::baseName($newControllerFile);
            $basePathController = str_replace($baseControllerClass, '', $newControllerFile);          
        }
        $controllerFile = (!empty($newControllerFile) ? ($basePathController . 'controllers/'. $baseControllerClass) : (Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\'))) . '.php'));

        $baseControllerFile = StringHelper::dirname($controllerFile) . '/base/' . StringHelper::basename($controllerFile);
        $restControllerFile = StringHelper::dirname($controllerFile) . '/api/' . StringHelper::basename($controllerFile);

        /*
         * search generated migration and overwrite it or create new
         */
        $migrationDir = StringHelper::dirname(StringHelper::dirname($controllerFile))
                . '/migrations';

        if (file_exists($migrationDir) && $migrationDirFiles = glob($migrationDir . '/m*_' . $controllerName . '00_access.php')) {
            $this->migrationClass = pathinfo($migrationDirFiles[0], PATHINFO_FILENAME);
        } else {
            $this->migrationClass = 'm' . date('ymd_Hi') . '00_' . $controllerName . '_access';
        }

        $files[] = new CodeFile($baseControllerFile, $this->render('controller.php', ['accessDefinitions' => $accessDefinitions]));
        $params['controllerClassName'] = \yii\helpers\StringHelper::basename($this->controllerClass);

        if ($this->overwriteControllerClass || !is_file($controllerFile)) {
            $files[] = new CodeFile($controllerFile, $this->render('controller-extended.php', $params));
        }

        if ($this->overwriteRestControllerClass || !is_file($restControllerFile)) {
            $files[] = new CodeFile($restControllerFile, $this->render('controller-rest.php', $params));
        }

        if (!empty($this->searchModelClass)) {
            $nsVendor = $this->createPathFromNs($this->searchModelClass, true);
            $searchModel = (!empty($nsVendor) ? $nsVendor : Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->searchModelClass, '\\')))) . '.php';
            if ($this->overwriteSearchModelClass || !is_file($searchModel)) {
                $files[] = new CodeFile($searchModel, $this->render('search.php'));
            }
        }

        $viewPath = $this->getViewPath();
        $templatePath = $this->getTemplatePath() . '/views';

        foreach (scandir($templatePath) as $file) {
            if (empty($this->searchModelClass) && $file === '_search.php') {
                continue;
            }
            if (is_file($templatePath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $files[] = new CodeFile("$viewPath/$file", $this->render("views/$file", ['permisions' => $permisions]));
            }
        }

        if ($this->generateAccessFilterMigrations) {

            /*
             * access migration
             */
            $migrationFile = $migrationDir . '/' . $this->migrationClass . '.php';
            $files[] = new CodeFile($migrationFile, $this->render('migration_access.php', ['accessDefinitions' => $accessDefinitions]));

            /*
             * access roles translation
             */
            $forRoleTranslationFile = StringHelper::dirname(StringHelper::dirname($controllerFile))
                    . '/messages/for-translation/'
                    . $controllerName . '.php';
            $files[] = new CodeFile($forRoleTranslationFile, $this->render('roles-translation.php', ['accessDefinitions' => $accessDefinitions]));
        }

        /*
         * create gii/[name]GiantCRUD.json with actual form data
         */
        $suffix = str_replace(' ', '', $this->getName());
        $controllerFileinfo = pathinfo($controllerFile);
        $formDataFile = StringHelper::dirname(StringHelper::dirname($controllerFile))
                . '/gii/'
                . str_replace('Controller', $suffix, $controllerFileinfo['filename']) . '.json';
        //$formData = json_encode($this->getFormAttributesValues());
        $formData = json_encode(SaveForm::getFormAttributesValues($this, $this->formAttributes()));
        $files[] = new CodeFile($formDataFile, $formData);

        return $files;
    }    

    public function generate() {
        try {
//            pr($this->relFiledsDynamic);

            $generator = $this->preGenerate();

            $this->getArrayForeignKeys();
            $this->getMmRelations();
//            $controllerFile = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php');
//            $translationFile = StringHelper::dirname($controllerFile).'/../i18n/it-IT/messages.php';
            $controllerClassName = \yii\helpers\StringHelper::basename($this->controllerClass);

//            $generatorFiles[] = new CodeFile($translationFile, $this->render('messages.php', [
//                'strings' => self::getTranslations($controllerClassName),
//                'controllerClassName' => $controllerClassName
//            ]));
//pr($this->arrayForeignKeys);
//            die;
            return $generator;
        } catch (Exception $ex) {
            return FALSE;
        }
    }

    /**
     * @param $module
     * @return array
     */
    public static function getTranslations($module) {
        $translationCacheName = 'mtc_' . $module;

        return (array) Yii::$app->cache->get($translationCacheName);
    }

    /**
     * @param $module
     * @param $slug
     * @param $text
     * @return bool
     */
    public static function addTranslation($module, $slug, $text) {
        $translationCacheName = 'mtc_' . $module;

        $moduleTranslations = Yii::$app->cache->get($translationCacheName);

        if (empty($moduleTranslations)) {
            $moduleTranslations = [];
        }

        $moduleTranslations[$slug] = $text;

        return true;
    }

    /**
     * @return string the controller ID (without the module ID prefix)
     */
    public function getControllerID() {
        $pos = strrpos($this->controllerClass, '\\');
        $class = substr(substr($this->controllerClass, $pos + 1), 0, -10);
        if ($this->singularEntities) {
            $class = Inflector::singularize($class);
        }

        return Inflector::camel2id($class, '-', true);
    }

    /**
     * @return string the controller view path
     */
    public function getViewPath() {
        if (empty($this->viewPath)) {
            return Yii::getAlias('@app/views/' . $this->getControllerID());
        }
        $path = null;
        $verify1 = $this->verifyControllerIdInPath($this->viewPath);
        if (!$verify1) {
            $path = Yii::getAlias(str_replace('\\', '/', $this->viewPath) . '/' . $this->getControllerID());
        } else {
            $path = Yii::getAlias(str_replace('\\', '/', $this->viewPath));
        }
        if (!is_dir($path) || empty($path)) {
            $path = $this->createPathFromNs($this->viewPath, false, true);
            if (!empty($path)) {
                $verify = $this->verifyControllerIdInPath($path);
                if (!$verify) {
                    $path = $path . '/' . $this->getControllerID();
                }
            }
        }
        return $path;
    }

    /**
     * 
     * @param string $path
     * @return boolean
     */
    protected function verifyControllerIdInPath($path){
        $controllerId = $this->getControllerID();
        $pathArray = explode('/', $path);
        $lastElement = end($pathArray);
        if($lastElement == $controllerId){
            return true;
        } else {
            return false;
        }
             
    }

    /**
     * @return string
     */
    public function getNameAttribute() {
        foreach ($this->getColumnNames() as $name) {
            if (!strcasecmp($name, 'name') || !strcasecmp($name, 'title')) {
                return $name;
            }
        }
        /* @var $class \yii\db\ActiveRecord */
        $class = $this->modelClass;
        $pk = $class::primaryKey();

        return $pk[0];
    }

    /**
     * Generates code for active field
     * @param string $attribute
     * @return string
     */
    public function generateActiveField($attribute) {
        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false || !isset($tableSchema->columns[$attribute])) {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $attribute)) {
                return "\$form->field(\$model, '$attribute')->passwordInput()";
            }

            return "\$form->field(\$model, '$attribute')";
        }
        $column = $tableSchema->columns[$attribute];
        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        }

        if ($column->type === 'text') {
            return "\$form->field(\$model, '$attribute')->textarea(['rows' => 6])";
        }

        if (preg_match('/^(password|pass|passwd|passcode)$/i', $column->name)) {
            $input = 'passwordInput';
        } else {
            $input = 'textInput';
        }

        if (is_array($column->enumValues) && count($column->enumValues) > 0) {
            $dropDownOptions = [];
            foreach ($column->enumValues as $enumValue) {
                $dropDownOptions[$enumValue] = Inflector::humanize($enumValue);
            }
            return "\$form->field(\$model, '$attribute')->dropDownList("
                    . preg_replace("/\n\s*/", ' ', VarDumper::export($dropDownOptions)) . ", ['prompt' => ''])";
        }

        if ($column->phpType !== 'string' || $column->size === null) {
            return "\$form->field(\$model, '$attribute')->$input()";
        }

        return "\$form->field(\$model, '$attribute')->$input(['maxlength' => true])";
    }

    /**
     * Generates code for active search field
     * @param string $attribute
     * @return string
     */
    public function generateActiveSearchField($attribute) {
        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false) {
            return "\$form->field(\$model, '$attribute')";
        }

        $column = $tableSchema->columns[$attribute];
        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->checkbox()";
        }

        return "\$form->field(\$model, '$attribute')";
    }

    /**
     * Generates column format
     * @param \yii\db\ColumnSchema $column
     * @return string
     */
    public function generateColumnFormat($column) {
        if ($column->phpType === 'boolean') {
            return 'boolean';
        }

        if ($column->type === 'text') {
            return 'ntext';
        }

        if (stripos($column->name, 'time') !== false && $column->phpType === 'integer') {
            return 'datetime';
        }

        if (stripos($column->name, 'email') !== false) {
            return 'email';
        }

        if (preg_match('/(\b|[_-])url(\b|[_-])/i', $column->name)) {
            return 'url';
        }

        return 'text';
    }

    /**
     * Generates validation rules for the search model.
     * @return array the generated validation rules
     */
    public function generateSearchRules() {
        if (($table = $this->getTableSchema()) === false) {
            return ["[['" . implode("', '", $this->getColumnNames()) . "'], 'safe']"];
        }
        $types = [];
        foreach ($table->columns as $column) {
            switch ($column->type) {
                case Schema::TYPE_TINYINT:
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                    $types['integer'][] = $column->name;
                    break;
                case Schema::TYPE_BOOLEAN:
                    $types['boolean'][] = $column->name;
                    break;
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                    $types['number'][] = $column->name;
                    break;
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                default:
                    $types['safe'][] = $column->name;
                    break;
            }
        }

        $rules = [];
        foreach ($types as $type => $columns) {
            $rules[] = "[['" . implode("', '", $columns) . "'], '$type']";
        }

        return $rules;
    }

    /**
     * @return array searchable attributes
     */
    public function getSearchAttributes() {
        return $this->getColumnNames();
    }

    /**
     * Generates the attribute labels for the search model.
     * @return array the generated attribute labels (name => label)
     */
    public function generateSearchLabels() {
        /* @var $model \yii\base\Model */
        $model = new $this->modelClass();
        $attributeLabels = $model->attributeLabels();
        $labels = [];
        foreach ($this->getColumnNames() as $name) {
            if (isset($attributeLabels[$name])) {
                $labels[$name] = $attributeLabels[$name];
            } else {
                if (!strcasecmp($name, 'id')) {
                    $labels[$name] = 'ID';
                } else {
                    $label = Inflector::camel2words($name);
                    if (!empty($label) && substr_compare($label, ' id', -3, 3, true) === 0) {
                        $label = substr($label, 0, -3) . ' ID';
                    }
                    $labels[$name] = $label;
                }
            }
        }

        return $labels;
    }

    /**
     * Generates search conditions
     * @return array
     */
    public function generateSearchConditions() {
        $columns = [];
        if (($table = $this->getTableSchema()) === false) {
            $class = $this->modelClass;
            /* @var $model \yii\base\Model */
            $model = new $class();
            foreach ($model->attributes() as $attribute) {
                $columns[$attribute] = 'unknown';
            }
        } else {
            foreach ($table->columns as $column) {
                $columns[$column->name] = $column->type;
            }
        }

        $likeConditions = [];
        $hashConditions = [];
        foreach ($columns as $column => $type) {
            switch ($type) {
                case Schema::TYPE_TINYINT:
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_BOOLEAN:
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DOUBLE:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $hashConditions[] = "'{$column}' => \$this->{$column},";
                    break;
                default:
                    $likeKeyword = $this->getClassDbDriverName() === 'pgsql' ? 'ilike' : 'like';
                    $likeConditions[] = "->andFilterWhere(['{$likeKeyword}', '{$column}', \$this->{$column}])";
                    break;
            }
        }

        $conditions = [];
        if (!empty($hashConditions)) {
            $conditions[] = "\$query->andFilterWhere([\n"
                    . str_repeat(' ', 12) . implode("\n" . str_repeat(' ', 12), $hashConditions)
                    . "\n" . str_repeat(' ', 8) . "]);\n";
        }
        if (!empty($likeConditions)) {
            $conditions[] = "\$query" . implode("\n" . str_repeat(' ', 12), $likeConditions) . ";\n";
        }

        return $conditions;
    }

    /**
     * Generates URL parameters
     * @return string
     */
    public function generateUrlParams() {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pks = $class::primaryKey();
        if (count($pks) === 1) {
            if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                return "'id' => (string)\$model->{$pks[0]}";
            }

            return "'id' => \$model->{$pks[0]}";
        }

        $params = [];
        foreach ($pks as $pk) {
            if (is_subclass_of($class, 'yii\mongodb\ActiveRecord')) {
                $params[] = "'$pk' => (string)\$model->$pk";
            } else {
                $params[] = "'$pk' => \$model->$pk";
            }
        }

        return implode(', ', $params);
    }

    /**
     * Generates action parameters
     * @return string
     */
    public function generateActionParams() {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $pks = $class::primaryKey();
        if (count($pks) === 1) {
            return '$' . $pks[0]; // fix for non-id columns
        }

        return '$' . implode(', $', $pks);
    }

    /**
     * @return array model column names
     */
    public function getColumnNames() {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        if (is_subclass_of($class, 'yii\db\ActiveRecord')) {
            return $class::getTableSchema()->getColumnNames();
        }

        /* @var $model \yii\base\Model */
        $model = new $class();

        return $model->attributes();
    }

    /**
     * @return string|null driver name of modelClass db connection.
     * In case db is not instance of \yii\db\Connection null will be returned.
     * @since 2.0.6
     */
    protected function getClassDbDriverName() {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;
        $db = $class::getDb();
        return $db instanceof \yii\db\Connection ? $db->driverName : null;
    }

    /**
     * @return array model column names
     */
    public function getRelationColumnNames() {
        /* @var $class ActiveRecord */
        $class = $this->modelClass;

        if (!empty($class) && is_subclass_of($class, 'yii\db\ActiveRecord')) {
            $tableSchema = $class::getTableSchema();

            $foreignKeys = $tableSchema->foreignKeys;
            $relations = [];

            foreach ($foreignKeys as $key => $value) {
//                pr($value);
                $tablename = $value[0];
                unset($value[0]);
                $fks = array_keys($value);
                $tableSchemaRel = \Yii::$app->db->getTableSchema($tablename, true);
                $relations[$tablename] = [
                    'fieldrename' => $fks,
                    'fields' => array_keys($tableSchemaRel->columns)];
            }
//            pr($relations); die;
            return $relations;
        } else if (!empty($class)) {
            /* @var $model \yii\base\Model */
            $model = new $class();

            return $model->attributes();
        } else {
            return [];
        }
    }

    public function getArrayForeignKeys() {
        $class = $this->modelClass;
        if (!empty($class)) {
            $tableSchema = $class::getTableSchema();
            $foreignKeys = $tableSchema->foreignKeys;
            $this->arrayForeignKeys = [];
            foreach ($foreignKeys as $refs) {
                unset($refs[0]);
                $this->arrayForeignKeys[] = array_keys($refs)[0];
            }
        }
        return $this->arrayForeignKeys;
    }

    /**
     *
     * "Relations": {
      "1918": {
      "type": "otm",
      "fromEntity": "agenda",
      "fromField": 1918,
      "fromFieldName": "persona",
      "fromModule": "modulo_personalizzato",
      "toFields": [{
      "toField": 1919,
      "toFieldName": "nome"
      }, {
      "toField": 1920,
      "toFieldName": "cognome"
      }],
      "toEntity": "persone",
      "toModule": "modulo_personalizzato",
      "descriptorField": "persona",
      "required": 0,
      "ordinal": 1
      }
      },
     *
     * @return int
     */
    public function getMmRelations() {


//pr($this->getRelationColumnNames());
//            pr($this->relFiledsDynamic);die;
        $mmRelationss = [];
        if (!empty($this->relFiledsDynamic)) {
            $pathsee = explode('\\', $this->modelClass);
            $name = array_pop($pathsee);
            $index = 0;
//            pr($this->getRelationColumnNames());
//            pr($this->relFiledsDynamic);die;
            foreach ($this->getRelationColumnNames() as $key => $value) {
//                pr($this->relFiledsDynamic[$key]['fields']);die;

                $mmRelationss[$index] = [
                    'type' => 'otm',
                    'fromEntity' => lcfirst($name),
                    'fromField' => $index,
                    'fromFieldName' => $value['fieldrename'][0],
                    'fromModule' => lcfirst($name),
                    'toFields' => [],
                ];
                $index2 = 0;
                if ($this->relFiledsDynamic[$key]['fields'] != null) {
                    foreach ($this->relFiledsDynamic[$key]['fields'] as $k => $v) {
                        $mmRelationss[$index]['toFields'] = [
                            'toField' => $index2,
                            'toFieldName' => $v,
                        ];
//                $tableSchemaRel = \Yii::$app->db->getTableSchema($value[0], true);
//                $relations = [$value[0] => array_keys($tableSchemaRel->columns)];

                        $index2++;
                    }
                }

                $mmRelationss[$index]['toEntity'] = $key;
                $mmRelationss[$index]['toModule'] = $this->relFiledsDynamic[$key]['moduleName'];
                $mmRelationss[$index]['descriptorField'] = $this->relFiledsDynamic[$key]['descriptorField'];
                $mmRelationss[$index]['required'] = $this->relFiledsDynamic[$key]['moduleRelRequired'];
                $mmRelationss[$index]['ordinal'] = 1;
                $index++;
            }
//            pr($mmRelationss);
//            die;
        }
        return $mmRelationss;
    }

    public function getMmRelationsSingle($attribute) {

        if (!empty($this->relFiledsDynamic)) {
            $pathsee = explode('\\', $this->modelClass);
            $name = array_pop($pathsee);
            $index = 0;
//            pr($this->getRelationColumnNames());
//            pr($this->relFiledsDynamic);die;
            foreach ($this->getRelationColumnNames() as $key => $value) {
//                pr($this->relFiledsDynamic[$key]['fields']);
//                die;
//                pr($attribute);
//                pr($value['fieldrename']);
//                die;
                if ((in_array($attribute, $value['fieldrename']))) {
                    $this->mmRelations = [
                        'type' => 'otm',
                        'fromEntity' => lcfirst($name),
                        'fromField' => $index,
                        'fromFieldName' => $value['fieldrename'][0],
                        'fromModule' => lcfirst($name),
                        'toFields' => [],
                    ];
                    $index2 = 0;
                    if (!empty($this->relFiledsDynamic[$key]['fields'])) {
                        foreach ($this->relFiledsDynamic[$key]['fields'] as $k => $v) {
                            $this->mmRelations['toFields'] = [
                                'toField' => $index2,
                                'toFieldName' => $v,
                            ];
//                $tableSchemaRel = \Yii::$app->db->getTableSchema($value[0], true);
//                $relations = [$value[0] => array_keys($tableSchemaRel->columns)];

                            $index2++;
                        }
                    }
//                    else {
//                        $this->mmRelations['toFields'] = [
//                            'toField' => $index2,
//                            'toFieldName' => 'id',
//                        ];
//                    }


                    $this->mmRelations['toEntity'] = $key;
                    $this->mmRelations['toModule'] = $this->relFiledsDynamic[$key]['moduleName'];
                    $this->mmRelations['descriptorField'] = $this->relFiledsDynamic[$key]['descriptorField'];
                    $this->mmRelations['required'] = $this->relFiledsDynamic[$key]['moduleRelRequired'];
                    $this->mmRelations['ordinal'] = 1;
                    $index++;
                }
            }
//            pr($this->mmRelations);
//            die;
        }
        return $this->mmRelations;
    }

    /**
     * @return string the controller ID (without the module ID prefix)
     */
    public function getModuleId() {
        if (!$this->moduleNs) {
            $controllerNs = \yii\helpers\StringHelper::dirname(ltrim($this->controllerClass, '\\'));
            $this->moduleNs = \yii\helpers\StringHelper::dirname(ltrim($controllerNs, '\\'));
        }

        return \yii\helpers\StringHelper::basename($this->moduleNs);
    }

    public static function getModelNameAttribute($modelClass) {
        $model = new $modelClass();
// TODO: cleanup, get-label-methods, move to config
        if ($model->hasMethod('get_label')) {
            return '_label';
        }
        if ($model->hasMethod('getLabel')) {
            return 'label';
        }
        if (method_exists($modelClass, 'getTableSchema')) {
            foreach ($model->getTableSchema()->getColumnNames() as $name) {
                switch (strtolower($name)) {
                    case 'name':
                    case 'title':
                    case 'name_id':
                    case 'default_title':
                    case 'default_name':
                    case 'ns'://name short
                    case 'nl'://name long
                        return $name;
                        break;
                    default:
                        continue;
                        break;
                }
            }
        }

        return $modelClass::primaryKey()[0];
    }

    public function getModelByTableName($name) {
        $returnName = str_replace($this->tablePrefix, '', $name);
        $returnName = Inflector::id2camel($returnName, '_');
        if ($this->singularEntities) {
            $returnName = Inflector::singularize($returnName);
        }

        return $returnName;
    }

    /**
     * Finds relations of a model class.
     *
     * return values can be filtered by types 'belongs_to', 'many_many', 'has_many', 'has_one', 'pivot'
     *
     * @param ActiveRecord $modelClass
     * @param array        $types
     *
     * @return array
     */
    public function getModelRelations($modelClass, $types = []) {
        $reflector = new \ReflectionClass($modelClass);
        $model = new $modelClass();
        $stack = [];
        $modelGenerator = new ModelGenerator();
        foreach ($reflector->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (in_array(substr($method->name, 3), $this->skipRelations)) {
                continue;
            }
// look for getters
            if (substr($method->name, 0, 3) !== 'get') {
                continue;
            }
// skip class specific getters
            $skipMethods = [
                'getRelation',
                'getBehavior',
                'getFirstError',
                'getAttribute',
                'getAttributeLabel',
                'getAttributeHint',
                'getOldAttribute',
            ];
            if (in_array($method->name, $skipMethods)) {
                continue;
            }
//don't call get functions if there is a parameter
            if (count($method->getParameters()) > 0) {
                continue;
            }
// check for relation
            try {
                $relation = @call_user_func(array($model, $method->name));
                if ($relation instanceof \yii\db\ActiveQuery) {
                    // detect relation
                    if ($relation->multiple === false) {
                        if (current($relation->link) == (new $relation->modelClass)->primaryKey()[0]) {
                            $relationType = 'has_one';
                        } else {
                            $relationType = 'belongs_to';
                        }
                    } elseif ($this->isPivotRelation($relation)) { // TODO: detecttion
                        $relationType = 'pivot';
                    } else {
                        $relationType = 'has_many';
                    }
                    // if types is empty, return all types -> no filter
                    if ((count($types) == 0) || in_array($relationType, $types)) {
                        $name = $modelGenerator->generateRelationName(
                                [$relation], $model->getTableSchema(), substr($method->name, 3), $relation->multiple
                        );
                        $stack[$name] = $relation;
                    }
                }
            } catch (\Exception $e) {
                \Yii::error('Error: ' . $e->getMessage(), __METHOD__);
            } catch (\Error $e) {
                //bypass get functions if calling to them results in errors (only for PHP7)
                \Yii::error('Error: ' . $e->getMessage(), __METHOD__);
            }
        }
        return $stack;
    }

    public function getColumnByAttribute($attribute, $model = null) {
        if (is_string($model)) {
            $model = new $model();
        }
        if ($model === null) {
            $model = $this;
        }

// omit schema for NOSQL models
        if (method_exists($model, 'getTableSchema') && $model->getTableSchema()) {
            return $model->getTableSchema()->getColumn($attribute);
        } else {
            return $attribute;
        }
    }

    /**
     * @param $column
     *
     * @return null|\yii\db\ActiveQuery
     */
    public function getRelationByColumn($model, $column, $types = ['belongs_to', 'many_many', 'has_many', 'has_one', 'pivot']) {
        $relations = $this->getModelRelations($model, $types);
        foreach ($relations as $relation) {
// TODO: check multiple link(s)
            if ($relation->link && reset($relation->link) == $column->name) {
                return $relation;
            }
        }

        return;
    }

    public function createRelationRoute($relation, $action) {
        $route = $this->pathPrefix . Inflector::camel2id(
                        $this->generateRelationTo($relation), '-', true
                ) . '/' . $action;

        return $route;
    }

    public function generateRelationTo($relation) {
        $class = new \ReflectionClass($relation->modelClass);
        $route = Inflector::variablize($class->getShortName());

        return $route;
    }

    public function isPivotRelation(ActiveQuery $relation) {
        $model = new $relation->modelClass();
        $table = $model->tableSchema;
        $pk = $table->primaryKey;
        if (count($pk) !== 2) {
            return false;
        }
        $fks = [];
        foreach ($table->foreignKeys as $refs) {
            if (count($refs) === 2) {
                if (isset($refs[$pk[0]])) {
                    $fks[$pk[0]] = [$refs[0], $refs[$pk[0]]];
                } elseif (isset($refs[$pk[1]])) {
                    $fks[$pk[1]] = [$refs[0], $refs[$pk[1]]];
                }
            }
        }
        if (count($fks) === 2 && $fks[$pk[0]][0] !== $fks[$pk[1]][0]) {
            return $fks;
        } else {
            return false;
        }
    }

    /**
     * @return array Class names of the providers declared directly under crud/providers folder
     */
    public static function getCoreProviders() {
        $files = FileHelper::findFiles(
                        __DIR__ . DIRECTORY_SEPARATOR . 'providers', [
                    'only' => ['*.php'],
                    'recursive' => false,
                        ]
        );

        foreach ($files as $file) {
            require_once $file;
        }

        return array_filter(
                get_declared_classes(), function ($a) {
            return stripos($a, __NAMESPACE__ . '\providers') !== false;
        }
        );
    }

    /**
     * @return array List of providers. Keys and values contain the same strings
     */
    public function generateProviderCheckboxListData() {
        $coreProviders = self::getCoreProviders();

        return array_combine($coreProviders, $coreProviders);
    }

    protected function initializeProviders() {
// TODO: this is a hotfix for an already initialized provider queue on action re-entry
        if ($this->_p !== []) {
            return;
        }

        if ($this->providerList) {
            foreach ($this->providerList as $class) {
                $class = trim($class);
                if (!$class) {
                    continue;
                }
                $obj = \Yii::createObject(['class' => $class]);
                $obj->generator = $this;
                $this->_p[] = $obj;
                //\Yii::trace("Initialized provider '{$class}'", __METHOD__);
            }
        }

        \Yii::trace("CRUD providers initialized for model '{$this->modelClass}'", __METHOD__);
    }

    /**
     * Generates code for active field by using the provider queue.
     *
     * @param ColumnSchema $column
     * @param null         $model
     *
     * @return mixed|string
     */
    public function activeField($attribute, $model = null) {
        if ($model === null) {
            $model = $this->modelClass;
        }
        $code = $this->callProviderQueue(__FUNCTION__, $attribute, $model, $this);
        if ($code !== null) {
            Yii::trace("found provider for '{$attribute}'", __METHOD__);

            return $code;
        } else {
            $column = $this->getColumnByAttribute($attribute);
            if (!$column) {
                return;
            } else {
                return self::generateActiveField($attribute);
            }
        }
    }

    public function prependActiveField($attribute, $model = null) {
        if ($model === null) {
            $model = $this->modelClass;
        }
        $code = $this->callProviderQueue(__FUNCTION__, $attribute, $model, $this);
        if ($code) {
            Yii::trace("found provider for '{$attribute}'", __METHOD__);
        }

        return $code;
    }

    public function appendActiveField($attribute, $model = null) {
        if ($model === null) {
            $model = $this->modelClass;
        }
        $code = $this->callProviderQueue(__FUNCTION__, $attribute, $model, $this);
        if ($code) {
            Yii::trace("found provider for '{$attribute}'", __METHOD__);
        }

        return $code;
    }

    public function columnFormat($attribute, $model = null) {
        if ($model === null) {
            $model = $this->modelClass;
        }
        $code = $this->callProviderQueue(__FUNCTION__, $attribute, $model, $this);
        if ($code !== null) {
            Yii::trace("found provider for '{$attribute}'", __METHOD__);
        } else {
            $code = $this->shorthandAttributeFormat($attribute, $model);
            Yii::trace("using standard formatting for '{$attribute}'", __METHOD__);
        }

        return $code;
    }

    public function attributeFormat($attribute, $model = null) {
        if ($model === null) {
            $model = $this->modelClass;
        }
        $code = $this->callProviderQueue(__FUNCTION__, $attribute, $model, $this);
        if ($code !== null) {
            Yii::trace("found provider for '{$attribute}'", __METHOD__);

            return $code;
        }

        $column = $this->getColumnByAttribute($attribute);
        if (!$column) {
            return;
        } else {
            return $this->shorthandAttributeFormat($attribute, $model);
        }
// don't call parent anymore
    }

    public function attributeEditable($attribute, $model = null) {
        if ($model === null) {
            $model = $this->modelClass;
        }
        $code = $this->callProviderQueue(__FUNCTION__, $attribute, $model, $this);
        if ($code !== null) {
            Yii::trace("found provider for '{$attribute}'", __METHOD__);

            return $code;
        }

        $column = $this->getColumnByAttribute($attribute);
        if (!$column) {
            return;
        } else {
            return $this->shorthandAttributeFormat($attribute, $model);
        }
// don't call parent anymore
    }

    public function render($template, $params = []) {
        $code = parent::render($template, $params);

// create temp file for code formatting
        $tmpDir = Yii::getAlias('@runtime/giiant');
        FileHelper::createDirectory($tmpDir);
        $tmpFile = $tmpDir . '/' . md5($template);
        file_put_contents($tmpFile, $code);

        if ($this->tidyOutput) {
            $command = Yii::getAlias('@vendor/bin/phptidy.php') . ' replace ' . $this->tidyOptions . ' ' . $tmpFile;
            shell_exec($command);
            $code = file_get_contents($tmpFile);
        }

        if ($this->fixOutput) {
            $command = Yii::getAlias('@vendor/bin/php-cs-fixer') . ' fix ' . $this->fixOptions . ' ' . $tmpFile;
            shell_exec($command);
            $code = file_get_contents($tmpFile);
        }

        unlink($tmpFile);

        return $code;
    }

    public function partialView($name, $model = null) {
        if ($model === null) {
            $model = $this->modelClass;
        }
        $code = $this->callProviderQueue(__FUNCTION__, $name, $model, $this);
        if ($code) {
            Yii::trace("found provider for partial view '{name}'", __METHOD__);
        }

        return $code;
    }

    public function relationGrid($name, $relation, $showAllRecords = false) {
        Yii::trace("calling provider queue for '$name'", __METHOD__);

        return $this->callProviderQueue(__FUNCTION__, $name, $relation, $showAllRecords);
    }

    public function relationGridEditable($name, $relation, $showAllRecords = false) {
        Yii::trace("calling provider queue for '$name'", __METHOD__);

        return $this->callProviderQueue(__FUNCTION__, $name, $relation, $showAllRecords);
    }

    protected function shorthandAttributeFormat($attribute, $model) {
// TODO: cleanup
        if (is_object($model) && (!method_exists($model, 'getTableSchema') || !$model->getTableSchema())) {
            return;
        }

        $column = $this->getColumnByAttribute($attribute, $model);
        if (!$column) {
            Yii::trace("No column for '{$attribute}' found", __METHOD__);

            return;
        } else {
            Yii::trace("Table column detected for '{$attribute}'", __METHOD__);
        }
        if ($column->phpType === 'boolean') {
            $format = 'boolean';
        } elseif ($column->type === 'text') {
            $format = 'ntext';
        } elseif (stripos($column->name, 'time') !== false && $column->phpType === 'integer') {
            $format = 'datetime';
        } elseif (stripos($column->name, 'email') !== false) {
            $format = 'email';
        } elseif (stripos($column->name, 'url') !== false) {
            $format = 'url';
        } else {
            $format = 'text';
        }

        return "        '" . $column->name . ($format === 'text' ? '' : ':' . $format) . "'";
    }

    protected function callProviderQueue($func, $args, $generator) {
// TODO: should be done on init, but providerList is empty
        $this->initializeProviders();

        $args = func_get_args();
        unset($args[0]);
// walk through providers
        foreach ($this->_p as $obj) {
            if (method_exists($obj, $func)) {
                $c = call_user_func_array(array(&$obj, $func), $args);
                // until a provider returns not null
                if ($c !== null) {
                    if (is_object($args)) {
                        $argsString = get_class($args);
                    } elseif (is_array($args)) {
                        $argsString = Json::encode($args);
                    } else {
                        $argsString = $args;
                    }
                    $msg = 'Using provider ' . get_class($obj) . '::' . $func . ' ' . $argsString;
                    Yii::trace($msg, __METHOD__);

                    return $c;
                }
            }
        }
    }

    public function validateClass($attribute, $params) {
        if ($this->singularEntities) {
            $this->$attribute = Inflector::singularize($this->$attribute);
        }
        parent::validateClass($attribute, $params);
    }

// TODO: replace with VarDumper::export
    public function var_export54($var, $indent = '') {
        switch (gettype($var)) {
            case 'string':
                return '"' . addcslashes($var, "\\\$\"\r\n\t\v\f") . '"';
            case 'array':
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    "
                            . ($indexed ? '' : $this->var_export54($key) . ' => ')
                            . $this->var_export54($value, "$indent    ");
                }

                return "[\n" . implode(",\n", $r) . "\n" . $indent . ']';
            case 'boolean':
                return $var ? 'TRUE' : 'FALSE';
            default:
                return var_export($var, true);
        }
    }
    
    /**
     *
     * @param type $namespace
     * @return string
     */
   /* public function createPathFromNs($namespace, $isSearch = true) {
        $path = null;
        try {
            $path = \Yii::getAlias('@' . str_replace('\\', '/', $namespace), false);
            $class = StringHelper::baseName($namespace);
            if (empty($path)) {
                $nsVendor = $this->modelClass;
                $reflector = new \ReflectionClass($nsVendor);
                $pathInVendor = $reflector->getFileName();
                if (!empty($pathInVendor)) {
                    $search = 'models' . '\\' . StringHelper::baseName($pathInVendor);                                      
                    $search2 = 'models' . '/' . StringHelper::baseName($pathInVendor);                                      
                    if ($isSearch == true) {
                        $path = str_replace($search, '', $pathInVendor) . 'search' . DIRECTORY_SEPARATOR . $class;
                    } else {
                        $path = str_replace( [$search,$search2], '', $pathInVendor). $class . '.php';
                    }
                }
            }
        } catch (\Exception $e) {
            
        }
        return $path;
    }*/

}
