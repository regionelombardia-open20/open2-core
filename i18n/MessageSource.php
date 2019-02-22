<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\i18n
 * @category   CategoryName
 */

namespace lispa\amos\core\i18n;


use lispa\amos\core\module\AmosModule;
use lajax\translatemanager\models\Language;
use lajax\translatemanager\models\LanguageSource;
use lajax\translatemanager\models\LanguageTranslate;
use yii\base\Exception;
use yii\i18n\DbMessageSource;
use Yii;

class MessageSource extends DbMessageSource
{

    /**
     * @var boolean enable autoUpdate db-files i18n
     */
    public $autoUpdate = false;

    /**
     * @var array More paths [key] => [pathstring] to parse for translations
     */
    public $extraCategoryPaths = [];
    
    /**     
     * @var string If the translation of the current language is not set you can translate it in the default language if it is set 
     */
    public $defaultLanguage;

    /**
     * @var array with results from database
     */
    protected static $dbCategoryCache = [];

    /**
     * @var array application's modules
     */
    private static $modules = null; //Singleton pattern


    public function init()
    {
        parent::init();
        try {
            if (!self::$modules) {
                self::$modules = [];
                $modulesByCategory = \Yii::$app->getModules(false);
                foreach ($modulesByCategory as $key => $module) {
                    $mod = $this->isAmosModule($key, $module);
                    if ($mod) {
                        self::$modules[$mod->getAmosUniqueId()] = $mod;
                    }
                }
            }
        }catch(Exception $ex){
            Yii::getLogger()->log($ex->getMessage(), \yii\log\Logger::LEVEL_ERROR);
        }
    }


    /**
     * @param $key
     * @param $module
     * @return AmosModule|null
     */
    private function isAmosModule($key, $module)
    {
        $amodModule = null;
        try {
            if (is_object($module)) {
                if ($module instanceof AmosModule) {
                    $amodModule = $module;
                }
            } elseif(isset($module['class'])) {
                $reflectionClass = new \ReflectionClass($module['class']);
                if ($reflectionClass->isSubclassOf(AmosModule::className())) {
                    $amodModule = \Yii::createObject($reflectionClass->getName(), [$key]);
                }
            }
        }catch (Exception $ex){
            Yii::getLogger()->log($ex->getMessage(), \yii\log\Logger::LEVEL_ERROR);
        }
        return  $amodModule;
    }

    /**
     * Tranlate wrapper with file fetching via HybridMessageSource
     * @param string $category
     * @param string $message
     * @param string $language
     * @return bool|string
     */

    public function translate($category, $message, $language)
    {
        try {
            $dbTranslation = $this->loadMessages($category, $language);
            if (!isset($dbTranslation[$message])) {
                if($this->autoUpdate) {
                    if (isset(self::$modules[$category])) {
                        if ($translation = $this->addTranslationByPath($category, $message, $language, self::$modules[$category]->getI18nDirPath())) {
                            $dbTranslation[$message] = $translation;
                            return $translation;
                        }
                    }

                    /**
                     * Parse extra translation paths
                     */
                    foreach ($this->extraCategoryPaths as $extraCategory => $extraPath) {
                        if ($extraCategory == $category) {
                            $translation = $this->addTranslationByPath($extraCategory, $message, $language, $extraPath);
                            if ($translation) {
                                $dbTranslation[$message] = $translation;
                                return $translation;
                            }
                        }
                    }
                }
            } else{
                return $dbTranslation[$message];
            }
        }catch (Exception $ex){
            Yii::getLogger()->log($ex->getMessage(), \yii\log\Logger::LEVEL_ERROR);
        }
        $ret = parent::translate($category, $message, $language);
        if ($ret === false) {
            if (!empty($this->defaultLanguage)) {
                $ret = parent::translate($category, $message, $this->defaultLanguage);
            }
            if ($ret === false) {
                self::$dbCategoryCache[$category] [$language] [$message] = $message;
            }
        }
        return $ret;
    }


    /**
     * @param $category
     * @param $message
     * @param $language
     * @param $pathLanguage
     * @return mixed
     */
    protected function addTranslationByPath($category, $message, $language, $pathLanguage) {
        /**
         * Get general translation configs
         */
        $translationsConfig = [];
        $translationsConfig['sourceLanguage'] = 'en-US';
        $translationsConfig['basePath'] = $pathLanguage;
        $translationsConfig['fileMap'] = [];
        $translationsConfig['fileMap'][$category] = 'messages.php';


        $messageSource = new HybridMessageSource($translationsConfig);
        return $this->alignDbAndTranslate($category, $message, $language, $messageSource);
    }

    /**
     * Align database with texts from module i18n files
     * @param $category
     * @param $message
     * @param $language
     * @param $messageSource
     * @return mixed
     */
    protected function alignDbAndTranslate($category, $message, $language, $messageSource) {
        /**
         * Active languages
         */
        $languages = Language::findAll(['status' => true]);

        /**
         * Find for existing source text
         */
        $currentTranslation = LanguageSource::findOne(['category' => $category, 'message' => $message]);

        /**
         * If not exists create a new one
         */
        if(!$currentTranslation || !$currentTranslation->id) {
            $currentTranslation = new LanguageSource([
                'category' => $category,
                'message' => $message
            ]);

            /**
             * Check validity or throw exception
             */
            if(!$currentTranslation->validate()) {
                throw new Exception('Unable to create language record, check validity');
            }

            /**
             * Save new source
             */
            $currentTranslation->save();
        }

        /**
         * Parse enabled languages
         */
        foreach ($languages as $singleLanguage) {
            /**
             * @var $translatedTexts array With translations in file
             */
            $translatedTexts = $messageSource->getAllMessages($category, $singleLanguage->language_id);

            /**
             * If the text is translated then insert the row
             */
            if(isset($translatedTexts[$message])) {
                $translationExists = LanguageTranslate::findOne([
                    'id' => $currentTranslation->id,
                    'language' => $singleLanguage->language_id
                ]);

                /**
                 * Create new one if not exists
                 */
                if(!$translationExists || !$translationExists->id) {
                    /**
                     * The new translated item
                     */
                    $newTranslation = new LanguageTranslate([
                        'id' => $currentTranslation->id,
                        'language' => $singleLanguage->language_id,
                        'translation' => $translatedTexts[$message]
                    ]);

                    /**
                     * Check for validity and save translation
                     */
                    if ($newTranslation->validate()) {
                        $newTranslation->save();

                        /**
                         * If cache category is not set create it
                         */
                        if(!isset(self::$dbCategoryCache[$category])) {
                            self::$dbCategoryCache[$category] = [];
                        }

                        /**
                         * If cache language by category is not set create it
                         */
                        if(!isset(self::$dbCategoryCache[$category][$language])) {
                            self::$dbCategoryCache[$category][$language] = [];
                        }

                        /**
                         * Set new message in cache
                         */
                        self::$dbCategoryCache[$category][$language][$currentTranslation->message] = $translatedTexts[$message];
                    }
                }
            }
        }

        //Return translation from message source
        return $messageSource->translate($category, $message, $language);
    }

    /**
     * @param string $category
     * @param string $language
     * @return array
     */
    protected function loadMessages($category, $language)
    {
        $messages = [];

        try {
            /**
             * If cached return cache
             */
            if (isset(self::$dbCategoryCache[$category]) && isset(self::$dbCategoryCache[$category][$language])) {
                if (!empty(self::$dbCategoryCache[$category][$language])) {
                    return self::$dbCategoryCache[$category][$language];
                }
            }

            /**
             * Load messages from db
             */
            $messages = parent::loadMessages($category, $language);

            /**
             * Create array if not exists
             */
            if (!isset(self::$dbCategoryCache[$category])) {
                self::$dbCategoryCache[$category] = [];
            }

            /**
             * Set new messages
             */
            self::$dbCategoryCache[$category][$language] = $messages;
        }catch (Exception $ex){
            Yii::getLogger()->log($ex->getMessage(), \yii\log\Logger::LEVEL_ERROR);
        }
        return $messages;
    }
}