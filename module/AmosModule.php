<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\module
 * @category   CategoryName
 */

namespace open20\amos\core\module;

use open20\amos\core\helpers\BreadcrumbHelper;
use open20\amos\core\widget\WidgetAbstract;
use ReflectionClass;
use ReflectionException;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class AmosModule
 * @package open20\amos\core\module
 */
abstract class AmosModule extends BaseAmosModule implements ModuleInterface
{
    /**
     * Array that will store the models used in the package
     * e.g. :
     * [
     *     'Comment' => 'frontend/models/comments/CommentModel'
     * ]
     *
     * The classes defined here will be merged with getDefaultModels()
     * having he manually defined by the user preference.
     *
     * @var array
     */
    public $modelMap = [];

    /**
     * @var array $defaultListViews This set the default views in lists
     */
    public $defaultListViews = ['grid'];

    /**
     * Configuration of the db fields will to translate
     * e.g. :
     * [
     *      [
     *           'namespace' => 'open20\amos\showcaseprojects\models\InitiativeType',
     *           //'connection' => 'db', //if not set it use 'db'
     *           //'classBehavior' => NULL,//if not set it use default classBehavior 'lajax\translatemanager\behaviors\TranslateBehavior'
     *           'attributes' => ['name', 'description'],
     *           'category' => 'amosshowcaseprojects',
     *      ],
     *      [
     *           'namespace' => 'open20\amos\showcaseprojects\models\InitiativeType',
     *           'attributes' => ['name', 'description'],
     *           'category' => 'amosshowcaseprojects',
     *      ]
     * ];
     * @var array $db_fields_translation
     */
    public $db_fields_translation;

    /**
     * @var bool $hideWidgetGraphicsActions
     */
    public $hideWidgetGraphicsActions = false;

    /**
     * @var bool $enableContentDuplication If true enable the content duplication on each row in table view
     */
    public $enableContentDuplication = false;

    /**
     * Return an instance of module
     *
     * @return AmosModule
     */
    public static function instance()
    {
        /*         * @var AmosModule $module */
        $module = Yii::$app->getModule(static::getModuleName());
        return $module;
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        // layout path configuration
        $layoutModule = Yii::$app->getModule('layout');

        if ($layoutModule) {
            $this->setLayoutPath($layoutModule->layoutPath);
        }

        $layoutModule = Yii::$app->getModule('layout');

        if ($layoutModule) {
            $this->setLayoutPath($layoutModule->layoutPath);
        }

        $this->defineModelClasses();

        if (!empty(Yii::$app->params['dashboardEngine']) && Yii::$app->params['dashboardEngine']
            == WidgetAbstract::ENGINE_ROWS) {
            $this->hideWidgetGraphicsActions = true;
        }
    }

    /**
     * @inheritdoc
     */
    public function getAmosUniqueId()
    {
        $uniqueIdAliasAmos = 'amos'.parent::getUniqueId();
        return $uniqueIdAliasAmos; // TODO: Change the autogenerated stub
    }

    /**
     * Merges the default and user defined model classes
     * Also let's the developer to set new ones with the
     * parameter being those the ones with most preference.
     *
     * @param array $modelClasses
     */
    public function defineModelClasses($modelClasses = [])
    {
        $this->modelMap = ArrayHelper::merge(
                $this->getDefaultModels(), $this->modelMap, $modelClasses
        );
    }

    /**
     * Get default model classes
     */
    protected abstract function getDefaultModels();

    /**
     * @param string $name
     * @return object
     * @throws InvalidConfigException
     */
    public function createModel($name, array $params = [])
    {
        return Yii::createObject($this->model($name), $params);
    }

    /**
     * Get defined className of model
     *
     * Returns an string or array compatible
     * with the Yii::createObject method.
     *
     * @param string $name
     * @param array $config // You should never send an array with a key defined as "class" since this will
     *                      // overwrite the main className defined by the system.
     * @return string|array
     */
    public function model($name)
    {
        $modelData = $this->modelMap[ucfirst($name)];
        return $modelData;
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    public function getI18nDirPath()
    {
        $rc = new ReflectionClass(get_class($this));
        return dirname($rc->getFileName()).DIRECTORY_SEPARATOR.'i18n';
    }

    /**
     * This method return the session key that must be used to add in session
     * the url from the user have started the content creation.
     * @return string
     */
    public static function beginCreateNewSessionKey()
    {
        return 'beginCreateNewUrl_'.static::getModuleName();
    }

    /**
     * This method return the session key that must be used to add in session
     * the url date and time creation from the user have started the content creation.
     * @return string
     */
    public static function beginCreateNewSessionKeyDateTime()
    {
        return 'beginCreateNewUrlDateTime_'.static::getModuleName();
    }

    
    
    public static function getModulesFrontEndMenus()
    {
        $menu = "";
        if (isset(Yii::$app->params['menuModules'])) {
            foreach (Yii::$app->params['menuModules'] as $module) {
                $menu .= Yii::$app->getModule($module)->getFrontEndMenu();
            }
        }
        return $menu;
    }

    /**
     *
     * @return type
     */
    public function getFrontEndMenu()
    {
        $menu = "";
        BreadcrumbHelper::reset();
        return $menu;
    }

    /**
     *
     * @param string $url
     * @return string
     */
    public static function toUrlModule($url)
    {
        $languageString = '/' . \Yii::$app->language;
        if(strncmp($url, $languageString, strlen($languageString)) === 0){
            $languageString = "";
        }
        return  $languageString .  '/' . static::getModuleName().$url;
    }

    /**
     *
     * @param type $title
     * @param type $link
     * @return string
     */
    protected function addFrontEndMenu($title, $link)
    {
        $itemMenu = Html::tag(
                'li',
                Html::a(
                    $title, null,
                    [
                        'class' => 'nav-link'.' '.'active',
                        'target' => '_self',
                        'title' => $title,
                        'href' => '/site/to-menu-url?url=' .  $link,
                    ]
                ),
                [
                    'class' => 'nav-item'.' '.'active',
                ]
        );
        return $itemMenu;
    }
}