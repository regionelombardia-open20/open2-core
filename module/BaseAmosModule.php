<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\module
 * @category   CategoryName
 */

namespace lispa\amos\core\module;

use lajax\translatemanager\helpers\Language as Lx;
use Yii;
use yii\base\Module as BaseModule;

/**
 * Class BaseAmosModule
 * @package lispa\amos\core\module
 */
abstract class BaseAmosModule extends BaseModule implements ModuleInterface
{
    private $rbacEnabled = true;

    /**
     * @var array The plugin metas eg Icons, Colors, Etc
     */
    public $pluginMetadata = [];
    
    /**
     * 
     * @return bool
     */
    public function getRbacEnabled()
    {
        return $this->rbacEnabled;
    }
    
    /**
     * 
     * @param bool $rbacEnabled
     */
    public function setRbacEnabled($rbacEnabled)
    {
        $this->rbacEnabled = $rbacEnabled;
    }
    
    /**
     * @param $category
     * @param $message
     * @param array $params
     * @param null $language
     * @return string
     */
    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t($category, $message, $params, $language);
    }

    /**
     * Metodo da usare per tutte le traduzioni che non fanno parte di attributi dei tag html (es. Non si può usare
     * nell'attributo "title" del tag "a")
     *
     *
     * @param string $category the message category.
     * @param string $message the message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     * @return string the translated message.
     */
    public static function tHtml($category, $message, $params = [], $language = null)
    {
        return Lx::t($category, $message, $params, $language);
    }

    /**
     * Metodo vecchio stile com'era nei plugin prima di introdurre le traduzioni a db.
     *
     *
     * @param string $category the message category.
     * @param string $message the message to be translated.
     * @param array $params the parameters that will be used to replace the corresponding placeholders in the message.
     * @param string $language the language code (e.g. `en-US`, `en`). If this is null, the current
     * [[\yii\base\Application::language|application language]] will be used.
     * @return string the translated message.
     */
    public static function tOld($category, $message, $params = [], $language = null)
    {
        return Yii::t('amos/' . static::getModuleName() . '/' . $category, $message, $params, $language);
    }

    /**
     * @return string
     */
    public function getAmosUniqueId()
    {
        $uniqueIdAliasAmos = 'amos' . parent::getUniqueId();

        return $uniqueIdAliasAmos; // TODO: Change the autogenerated stub
    }
}
