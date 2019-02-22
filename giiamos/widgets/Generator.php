<?php
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\giiamos\widgets
 * @category   CategoryName
 */

namespace lispa\amos\core\giiamos\widgets;

use Yii;
use yii\gii\CodeFile;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

class Generator extends \yii\gii\generators\model\Generator
{
    public $ns_4class;
    public $vendorPath;

    /**
     * @var nama space for migrations file
     */
    public $ns_4migrations;

    /**
     * @var name of the migration files
     */
    public $migrationName;

    /**
     * @var array contains the color's list
     */
    public $iconColor;
    public $iconClass;

    /**
     * override $ns Generator
     */
    public $ns = 'app\\modules\\#module_name#\\widgets\\icons';

    /**
     *
     */
    public $moduleName;

    /**
     *
     */
    public $widgetType;

    /**
     *
     */
    public $widgetLabel;
    //widget name used to save
    public $widgetName;
    //widget name request on the form: DO NOT SAVE IT
    public $widgetNameInput;
    public $widgetUrl;
    public $widgetDescription;

    /**
     *
     */
    public $pluginName;

    /**
     *
     */
    public $widgetScope;

    /**
     * array containing all the module's names
     */
    public $modulesNames;

    /**
     * array containing all the module's names to EXClUDE
     */
    public $excludeModule;
    public $widgetFather;
    public $allRoles;
    public $rolesSelected;
    public $widgetVisible;
    public $iconFramework;
    public $migration_widget_filename;
    public $migration_auth_filename;

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Amos Widgets';
    }

    public function init()
    {
        parent::init();
        //module names to exlcude
        $this->excludeModule = ['cwh', 'tag', 'translatemanager', 'redactor', 'gridview', 'datecontrol', 'admin',
            'upload', 'dashboard', 'audit', 'comuni', 'treemanager', 'workflow',
            'amministra-utenti', 'file', 'slideshow', 'debug', 'gii', 'social'
        ];

        //get all the module names, excluding the above mentioned
        $this->modulesNames = $this->getNameModules();

        $this->allRoles = \Yii::$app->authManager->getRoles();
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return 'This generator generates an ActiveRecord class and base class for the specified database table.';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['iconColor', 'iconClass', 'moduleName', 'widgetLabel', 'widgetNameInput', 'widgetUrl'], 'required'],
            [['widgetFather'], 'required',
                'when' => function($model) {
                    return false; //widget son
                },
                'whenClient' => "function (attribute, value) {
                        return $('#radio_widgetscope :checked').val() == 'radio_son';
                    }"
            ],
            [['iconColor', 'iconClass', 'vendorPath'], 'string'],
            [['tablePrefix', 'widgetScope', 'widgetFather', 'moduleName', 'widgetNameInput', 'widgetDescription', 'widgetType',
                'iconColor', 'iconClass', 'widgetUrl', 'migrationName', 'rolesSelected', 'widgetVisible', 'iconFramework'],
                'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'iconColor' => 'Widget Icon Color',
            'vendorPath' => 'Percorso relativo dentro la vendor',
            'iconClass' => 'Widget Icon Class',
            'widgetLabel' => 'Widget Label',
            'widgetNameInput' => 'Widget Name (without prefix \'WidgetIcon\' or \'WidgetGraphic\')',
            'widgetFather' => 'Widget Father',
            'widgetUrl' => 'Widget URL',
            'moduleName' => 'Nome Modulo',
            'widgetScope' => 'Widget Scope',
            'widgetDescription' => 'Widget Description',
            'rolesSelected' => 'Roles',
            'widgetVisible' => 'Widget Visibile Dashboard principale',
            'iconFramework' => 'Tipologia framework icone',
        ];
    }

    /**
     * @inheritdoc
     */
    public function hints()
    {
        return [
            'iconClass' => 'Icon class',
            'vendorPath' => 'Percorso relativo dentro la vendor per esempio "lispa/amos-news/src/widgets"',
            'iconColor' => 'Icon colors',
            'label' => 'Widget class',
            'moduleName' => 'Module Name eg: modulo_di_test',
            'widgetUrl' => 'URL destination e.g /module_name/action-to-call/index',
            'rolesSelected' => 'Roles to associate at the widget',
            'widgetScope' => "<b>Widget father:</b>widget di 1° livello, predisposto per la navigazione a 2 livelli<br/><b>Widget son:</b>widget di 2° livello, occorre selezionare il widget padre<br/><b>Widget stand-alone:</b>widget di 1° livello, a sé stante",
            'widgetVisible' => "<b>CONTROLLARE</b> di avere Dashboard >= test/1.8.3",
        ];
    }

    /**
     * @inheritdoc
     */
    public function requiredTemplates()
    {
        return ['WidgetIconFiglio.php', 'WidgetIconPadre.php', 'WidgetIconStandAlone.php', 'WidgetMigrationAmosWidget.php'];
    }

    /**
     * gets all the modules and return an array with the respective name
     * NOTE: will be excluded the modules in the variable '$this->excludeModule'
     * @return array
     */
    public function getNameModules()
    {
        $all_modules = \Yii::$app->getModules();

        $ret_arr = array();

        foreach ($all_modules as $id_modulo => $mod) {
            if (!in_array($id_modulo, $this->excludeModule)) {
                $ret_arr[$id_modulo] = $id_modulo;
            }
        }

        return $ret_arr;
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        try {
            $files = [];
            //$relations = $this->generateRelations();
            //$db = $this->getDbConnection();
//pr($_POST, "post");
//pr($this->toArray(), "this");
//pr($this->migrationName, "migrationName");
            if (!empty($this->widgetFather)) {
                $_SESSION['widgetFather'] = $this->widgetFather;
            }

            //get the module object
            $ModObj = \Yii::$app->getModule($this->moduleName);

            if (is_object($ModObj)) {
                //get the complete path of the file
                $path             = $ModObj->getBasePath();
                //get the namespace of the module (not the clean one, with an addictional subpath e.g \Module )
                $namespace_dirty  = get_class($ModObj);
                //get addictional subpath above nominatad
                $str_module       = StringHelper::baseName($namespace_dirty);
                //remove the addictional path from the namaspace
                $module_namespace = str_replace($str_module, '', $namespace_dirty);
                //pr($module_namespace, "NAMASPACE");
                $this->ns_4class  = $module_namespace.'widgets\\icons';
                if (strtolower($this->widgetType) == 'graphics') {
                    $this->ns_4class = $module_namespace.'widgets\\graphics';
                }
                $this->ns_4migrations = $module_namespace.'migrations';
            }

            $params = [
                'data_obj' => $this,
            ];
            //file name of the migration
            if (array_key_exists('preview', $_POST)) {
                //$this->migrationName = 'm'.date('ymd_His').'_add_amos_widgets';
                $this->migrationName = 'm'.date('ymd_His');
            }
            //$this->migrationName = 'm'.date('ymd_His').'_add_amos_widgets';
            //if( !empty($this->widgetName) ){
            $this->widgetName = "Widget".Inflector::camelize(strtolower($this->widgetType)).$this->widgetNameInput;
            //}
            //$widgetClassFile = Yii::getAlias('@' . str_replace('\\', '/', $this->ns_4class)) . '/' . $this->moduleName . '.php';
            // $nsVendor           = $this->createPathFromNamespace($this->ns_4class);

            $nsVendor    = null;
            $nsMigration = null;
            if (!empty($this->vendorPath)) {
                $nsVendor    = $this->createPathFromNamespace();
                $nsMigration = $this->createPathFromNamespace(true);
            }

            $widgetClassFile = (!empty($nsVendor) ? $nsVendor : Yii::getAlias('@'.str_replace('\\', '/',
                        $this->ns_4class))).'/'.$this->widgetName.'.php';
            //$migrationClassFile = Yii::getAlias('@' . str_replace('\\', '/', $this->ns_4migrations)) . '/' . $this->migrationName;

            $migrationClassFile = (!empty($nsMigration) ? $nsMigration : Yii::getAlias('@'.str_replace('\\', '/',
                        $this->ns_4migrations))).'/';
//pr($widgetClassFile, "path 4 clas");
//pr($migrationClassFile, "path 4 migration");
            //if( !empty($this->widgetName) ){
            $this->widgetName   = "Widget".Inflector::camelize(strtolower($this->widgetType)).$this->widgetNameInput;
            //}
            //if the widget's type is GRAPHICS
            if (strtolower($this->widgetType) == 'graphics') {
                //generate widget's CLASS
                $files[] = new CodeFile(
                    $widgetClassFile, $this->render('WidgetIconGraphics.php', $params)
                );
            }
            //if the widget's type is ICON
            else {
                //generate widgets's CLASS
                if ($this->widgetScope == 'radio_father') {
                    $files[] = new CodeFile(
                        $widgetClassFile, $this->render('WidgetIconPadre.php', $params)
                    );
                }
                if ($this->widgetScope == 'radio_son') {
                    $files[] = new CodeFile(
                        $widgetClassFile, $this->render('WidgetIconFiglio.php', $params)
                    );
                }
                if ($this->widgetScope == 'radio_standalone') {
                    $files[] = new CodeFile(
                        $widgetClassFile, $this->render('WidgetIconStandAlone.php', $params)
                    );
                }
            }
            $underscored_widget_name = Inflector::underscore($this->widgetNameInput);

            $this->migration_widget_filename = $this->migrationName.'_add_amos_widgets_'.$underscored_widget_name;
            $this->migration_auth_filename   = $this->migrationName.'_add_auth_item_'.$underscored_widget_name;
            //generate the mig
            //rations
            $files[]                         = new CodeFile(
                $migrationClassFile.$this->migration_widget_filename.'.php',
                $this->render('WidgetMigrationAmosWidget.php', $params)
            );
            $files[]                         = new CodeFile(
                $migrationClassFile.$this->migration_auth_filename.'.php',
                $this->render('WidgetMigrationAuthItem.php', $params)
            );

            //pr($files, "ff");
            return $files;
        } catch (\Exception $e) {
            pr($e->getMessage());
            pr($e->getFile().' '.$e->getLine());
            die;
            return NULL;
        }
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
            $relations = parent::generateRelations();

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

    /**
     * Validates the namespace.
     *
     * @param string $attribute Namespace variable.
     */
    public function validateNamespace($attribute)
    {
        $value = $this->$attribute;
        $value = ltrim($value, '\\');
        $path  = Yii::getAlias('@'.str_replace('\\', '/', $value), false);

        if (!empty($this->vendorPath)) {
            $path = $this->createPathFromNamespace();
        }
        
        if (empty(path)) {
            $this->addError($attribute, 'Namespace must be associated with an existing directory.');
        }
    }

    /**
     *
     * @param boolean $migration
     * @return string|null
     */
    public function createPathFromNamespace($migration = false)
    {
        $path = null;
        try {
            $basePath = Yii::getAlias('@'.str_replace('\\', '/', "vendor"), false);
            if ($migration == true) {
                $path          = $basePath;
                $search        = StringHelper::baseName($this->vendorPath);
                $pathMigration = str_replace([$search, '/'.$search, '\\'.$search], '', $this->vendorPath);
                $path          = $basePath.DIRECTORY_SEPARATOR.$pathMigration.'migrations';
            } else {
                $path = $basePath.DIRECTORY_SEPARATOR.$this->vendorPath;
            }
        } catch (\Exception $e) {

        }
        return $path;
    }
}