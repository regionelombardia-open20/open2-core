<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\migration
 * @category   CategoryName
 */

namespace open20\amos\core\migration;

use open20\amos\core\migration\libs\common\MigrationCommon;
use open20\amos\core\module\BaseAmosModule;
use lajax\translatemanager\models\Language;
use lajax\translatemanager\models\LanguageSource;
use lajax\translatemanager\models\LanguageTranslate;
use Yii;
use yii\db\Migration;

/**
 * Class AmosMigrationTranslations
 *
 * This class is useful to add translations to db if the translatemanager module is present in the project.
 * The only method to override is "setTranslations". This method return an array of array of array in which you can set
 * the translations that you want to add. In this array you can specify the translations for various languages.
 * In each single translation array you can specify an alternative language, different from the major one. Then you must specify
 * the category, the source message and the translation message. In the class are specified some constants for the major languages.
 * All the translations set in a major language are assumed of this language.
 * Note: when reverting a translations migration are removed only the translations and not the source in order to preserve for other use.
 * If you want to remove even the source you must set the 'removeSource' key in a single translation array.
 * The array must be in the format described below.
 *
 * [
 *      'LANG_TO_TRANSLATE' => [
 *          [
 *              'language' => 'ALTERNATIVE_LANG_TO_TRANSLATE' (optional alternative language)
 *              'category' => 'CATEGORY_OF_THE_MESSAGE',
 *              'source' => 'THE_MESSAGE_SOURCE',
 *              'translation' => 'THE_MESSAGE_TRANSLATION',
 *              'removeSource' => true
 *          ],
 *          .
 *          .
 *          .
 *      ],
 *      .
 *      .
 *      .
 * ]
 *
 *
 * Here is an example of a single message translation from a source to italian language and the same message translated in French language
 * with the optional 'language' key.
 *
 * [
 *      self::LANG_IT => [
 *          [
 *              'category' => 'amoscore',
 *              'source' => 'Save',
 *              'translation' => 'Salva',
 *          ],
 *          [
 *              'language' => self::LANG_FR (optional alternative language)
 *              'category' => 'amoscore',
 *              'source' => 'Save',
 *              'translation' => 'Enregistrer',
 *              'removeSource' => true
 *          ],
 *          .
 *          .
 *          .
 *      ],
 *      .
 *      .
 *      .
 * ],
 *
 * @package open20\amos\core\migration
 */
class AmosMigrationTranslations extends Migration
{
    // List of constants of the most common languages.
    const LANG_IT = 'it-IT';
    const LANG_EN_GB = 'en-GB';
    const LANG_EN_US = 'en-US';
    const LANG_ES = 'es-ES';
    const LANG_FR = 'fr-FR';
    const LANG_DE = 'de-DE';
    
    // Field types
    const FIELD_TYPE_STRING = 'STRING';
    const FIELD_TYPE_INT = 'INT';
    const FIELD_TYPE_ARRAY = 'ARRAY';
    const FIELD_TYPE_BOOL = 'BOOL';
    
    /**
     * @var array $fieldTypes This is internal configurations useful to check the integrity of the array content.
     */
    private $fieldTypes = [
        'category' => self::FIELD_TYPE_STRING,
        'source' => self::FIELD_TYPE_STRING,
        'translation' => self::FIELD_TYPE_STRING,
        'language' => self::FIELD_TYPE_STRING,
        'removeSource' => self::FIELD_TYPE_BOOL,
        'update' => self::FIELD_TYPE_BOOL,
        'newTranslation' => self::FIELD_TYPE_STRING,
        'oldTranslation' => self::FIELD_TYPE_STRING
    ];
    
    /**
     * @var array $translations Translations array. These are inserted in the database
     */
    private $translations = [];
    
    /**
     * @var array $requiredFields Required fields in a single translation array.
     */
    private $requiredFields = [
        'category',
        'source',
        'translation'
    ];
    
    /**
     * @var array $requiredUpdateFields Required fields in a single translation update array.
     */
    private $requiredUpdateFields = [
        'category',
        'source',
        'newTranslation',
        'oldTranslation'
    ];
    
    /**
     * @var array $allowedFields All allowed fields for a single configuration array.
     */
    private $allowedFields = [
        'category',
        'source',
        'translation',
        'language',
        'removeSource',
        'update',
        'newTranslation',
        'oldTranslation'
    ];
    
    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();
        $this->db->enableSchemaCache = false;
        
        $this->translations = $this->setTranslations();
        
        if (empty($this->translations)) {
            throw new \Exception(BaseAmosModule::t('amoscore', 'The translations configuration array is empty'));
        }
        
        $isOkArrayStructure = $this->checkArrayStructure();
        if (!$isOkArrayStructure) {
            throw new \Exception(BaseAmosModule::t('amoscore', 'The structure of the translations configuration array is not correct') . $this->_errors);
        }
    }
    
    /**
     * Override this method to set all the translations configurations.
     * @return array
     */
    protected function setTranslations()
    {
        return [];
    }
    
    /**
     * This method checks the entire array structure element by element and set the errors in the global variable $this->_errors used in the exception message to print errors to developer.
     * @return bool
     */
    private function checkArrayStructure()
    {
        $allOk = true;
        
        foreach ($this->translations as $language => $translationConfs) {
            
            // Check if language is a string
            if (!is_string($language)) {
                $allOk = false;
                $this->_errors .= "\n" . BaseAmosModule::t('amoscore', 'The language index is not a string');
            }
            
            // Check if translations conf array is an array
            if (!is_array($translationConfs)) {
                $this->_errors .= "\n" . BaseAmosModule::t('amoscore', 'Translation confs element is not an array');
                $allOk = false;
                continue;
            }
            
            // Check language configurations
            foreach ($translationConfs as $index => $translationConf) {
                $ok = $this->checkSingleTranslationConfiguration($translationConf);
                if (!$ok) {
                    $allOk = false;
                }
            }
        }
        return $allOk;
    }
    
    /**
     * @param array $translationConf A single translation configuration.
     * @return bool
     */
    private function checkSingleTranslationConfiguration($translationConf)
    {
        // Check required fields
        $ok = $this->checkRequiredFields($translationConf);
        if (!$ok) {
            return false;
        }
        
        $allOk = true;
        foreach ($translationConf as $fieldName => $fieldValue) {
            if (!in_array($fieldName, $this->allowedFields)) {
                $this->_errors .= "\n'$fieldName' " . BaseAmosModule::t('amoscore', 'is not allowed');
                continue;
            }
            
            $ok = $this->checkFieldType($fieldName, $this->fieldTypes[$fieldName], $translationConf);
            if (!$ok) {
                MigrationCommon::printCheckStructureError($translationConf, BaseAmosModule::t('amoscore', 'The content of the field') . " '$fieldName' " . BaseAmosModule::t('amoscore', "is of incorrect type. It must be") . " " . $this->fieldTypes[$fieldName] . ".");
                $allOk = false;
            }
        }
        return $allOk;
    }
    
    /**
     * Check if a field is required.
     * @param array $translationConf A single translation configuration.
     * @return bool
     */
    private function checkRequiredFields($translationConf)
    {
        if (isset($translationConf['update']) && $translationConf['update']) {
            return $this->checkUpdateRequiredFields($translationConf);
        } else {
            return $this->checkStandardRequiredFields($translationConf);
        }
    }
    
    /**
     * Check the standard translation array (case of add of new translation).
     * @param array $translationConf A single translation configuration.
     * @return bool
     */
    private function checkStandardRequiredFields($translationConf)
    {
        $ok = true;
        foreach ($this->requiredFields as $requiredField) {
            if (!isset($translationConf[$requiredField])) {
                $this->_errors .= "\n'$requiredField' " . BaseAmosModule::t('amoscore', 'is required but not present in add.');
                $ok = false;
            }
        }
        return $ok;
    }
    
    /**
     * Check the update translation array (case of update of an existent translation).
     * @param array $translationConf A single translation configuration.
     * @return bool
     */
    private function checkUpdateRequiredFields($translationConf)
    {
        $ok = true;
        foreach ($this->requiredUpdateFields as $requiredUpdateField) {
            if (!isset($translationConf[$requiredUpdateField])) {
                $this->_errors .= "\n'$requiredUpdateField' " . BaseAmosModule::t('amoscore', 'is required but not present in update.');
                $ok = false;
            }
        }
        return $ok;
    }
    
    /**
     * Method that checks the correct type of a field value.
     *
     * @param string $fieldName Name of an internal array field.
     * @param string $fieldType Value type of an internal array field.
     * @param array $translationConf One internal array.
     *
     * @return bool Returns true if everything goes well. False otherwise.
     */
    private function checkFieldType($fieldName, $fieldType, $translationConf)
    {
        switch ($fieldType) {
            case self::FIELD_TYPE_STRING:
                $ok = is_string($translationConf[$fieldName]);
                break;
            case self::FIELD_TYPE_INT:
                $ok = is_numeric($translationConf[$fieldName]);
                break;
            case self::FIELD_TYPE_ARRAY:
                $ok = is_array($translationConf[$fieldName]);
                break;
            case self::FIELD_TYPE_BOOL:
                $ok = is_bool($translationConf[$fieldName]);
                break;
            default:
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Invalid type for field') . ' ' . $fieldName);
                $ok = false;
                break;
        }
        return $ok;
    }
    
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        /** @var \lajax\translatemanager\Module $translateManagerModule */
        $translateManagerModule = Yii::$app->getModule('translatemanager');
        if (!isset($translateManagerModule)) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'translatemanager module not present. Skip adding translations.'));
            return true;
        }
        
        $ok = $this->beforeAddConfs();
        if ($ok) {
            $ok = $this->addAllTranslations();
        }
        if ($ok) {
            $ok = $this->afterAddConfs();
        }
        
        return $ok;
    }
    
    /**
     * Override this to make operations before adding the translations configurations.
     * @return bool
     */
    protected function beforeAddConfs()
    {
        return true;
    }
    
    /**
     * Override this to make operations after adding the translations configurations.
     * @return bool
     */
    protected function afterAddConfs()
    {
        return true;
    }
    
    /**
     * This method add all translations configurations set in the global array. It verify if a configuration is already present.
     * If the configuration not exists the method create it, otherwise it goes over.
     * @return  boolean
     */
    private function addAllTranslations()
    {
        $ok = true;
        foreach ($this->translations as $language => $translationConfs) {
            $ok = $this->addLanguageTranslations($language, $translationConfs);
            if (!$ok) {
                break;
            }
        }
        return $ok;
    }
    
    /**
     * Method to add all translations.
     * @param string $language The general language of the configurations.
     * @param array $translationConfs Key => value array that contains a translations configuration.
     */
    private function addLanguageTranslations($language, $translationConfs)
    {
        $ok = true;
        foreach ($translationConfs as $translationConf) {
            if (isset($translationConf['language'])) {
                $language = $translationConf['language'];
            }
            if (!$this->languageExists($language)) {
                return false;
            }
            $ok = $this->addLanguageTranslation($language, $translationConf);
            if (!$ok) {
                break;
            }
        }
        return $ok;
    }
    
    /**
     * Check if a language exists.
     * @param $language
     * @return bool
     */
    private function languageExists($language)
    {
        return Language::find()->andWhere(['language_id' => $language])->exists();
    }
    
    /**
     * Find a language source by category and message.
     * @param string $category The category of the message source.
     * @param string $message The source message.
     * @return LanguageSource|null
     */
    private function findLanguageSource($category, $message)
    {
        $result = LanguageSource::find()->andWhere(['like binary', 'category', $category])->andWhere(['like binary', 'message', $message])->all();
        $retVal = ((count($result) == 1) ? $result[0] : null);
        return $retVal;
    }
    
    /**
     * Find a language source by category and message.
     * @param string $languageSourceId The language source id.
     * @param string $language The translate language.
     * @return LanguageTranslate|null
     */
    private function findLanguageTranslate($languageSourceId, $language)
    {
        return LanguageTranslate::findOne(['id' => $languageSourceId, 'language' => $language]);
    }
    
    /**
     * Method useful to add a single translation configuration.
     * @param string $language The general language of the configurations.
     * @param array $translationConf Key => value array that contains a translations configuration.
     * @return bool
     */
    private function addLanguageTranslation($language, $translationConf)
    {
        $ok = false;
        $languageSource = $this->addLanguageSource($translationConf['category'], $translationConf['source']);
        if (!is_null($languageSource)) {
            if (isset($translationConf['update']) && $translationConf['update']) {
                $ok = $this->updateLanguageTranslate($languageSource->id, $language, $translationConf['newTranslation']);
            } else {
                $ok = $this->addLanguageTranslate($languageSource->id, $language, $translationConf['translation']);
            }
        }
        return $ok;
    }
    
    /**
     * @param string $category The category of the language source.
     * @param string $message The source message.
     * @return null|LanguageSource
     */
    private function addLanguageSource($category, $message)
    {
        $languageSource = $this->findLanguageSource($category, $message);
        if (is_null($languageSource)) {
            $languageSource = new LanguageSource();
            $languageSource->category = $category;
            $languageSource->message = $message;
            $ok = $languageSource->save();
            if (!$ok) {
                $languageSource = null;
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Language source creation error => category') . ': '  . $category .  '; ' . BaseAmosModule::t('amoscore', 'message') . ': ' . $message);
            } else {
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Language source successfully created => category') . ': '  . $category .  '; ' . BaseAmosModule::t('amoscore', 'message') . ': ' . $message);
            }
        } else {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Language source already exists. Skipping... => category') . ': '  . $category .  '; ' . BaseAmosModule::t('amoscore', 'message') . ': ' . $message);
        }
        return $languageSource;
    }
    
    /**
     * @param int $languageSourceId The language source id.
     * @param string $language The language of the translation.
     * @param string $translation The source message translation.
     * @return bool
     */
    private function addLanguageTranslate($languageSourceId, $language, $translation)
    {
        $ok = true;
        $languageTranslate = $this->findLanguageTranslate($languageSourceId, $language);
        if (is_null($languageTranslate)) {
            $languageTranslate = new LanguageTranslate();
            $languageTranslate->id = $languageSourceId;
            $languageTranslate->language = $language;
            $languageTranslate->translation = $translation;
            $ok = $languageTranslate->save();
            if (!$ok) {
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Language translate creation error => languageSourceId') . ': '  . $languageSourceId . '; ' . BaseAmosModule::t('amoscore', 'language') . ': ' . $language . '; ' . BaseAmosModule::t('amoscore', 'translation') . ': ' . $translation);
            } else {
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Language translate successfully created => languageSourceId') . ': '  . $languageSourceId . '; ' . BaseAmosModule::t('amoscore', 'language') . ': ' . $language . '; ' . BaseAmosModule::t('amoscore', 'translation') . ': ' . $translation);
            }
        } else {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Language translate already exists. Skipping... => languageSourceId') . ': '  . $languageSourceId . '; ' . BaseAmosModule::t('amoscore', 'language') . ': ' . $language . '; ' . BaseAmosModule::t('amoscore', 'translation') . ': ' . $translation);
        }
        return $ok;
    }
    
    /**
     * Method useful to update a translation. If the translation is not found it return an error.
     * @param int $languageSourceId The language source id.
     * @param string $language The language of the translation.
     * @param string $translation The update source message translation.
     * @return bool
     */
    private function updateLanguageTranslate($languageSourceId, $language, $translation)
    {
        $ok = true;
        $languageTranslate = $this->findLanguageTranslate($languageSourceId, $language);
        if (!is_null($languageTranslate)) {
            $languageTranslate->translation = $translation;
            $ok = $languageTranslate->save();
            if (!$ok) {
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Language translate update error => languageSourceId') . ': '  . $languageSourceId . '; ' . BaseAmosModule::t('amoscore', 'language') . ': ' . $language . '; ' . BaseAmosModule::t('amoscore', 'translation') . ': ' . $translation);
            } else {
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Language translate successfully updated => languageSourceId') . ': '  . $languageSourceId . '; ' . BaseAmosModule::t('amoscore', 'language') . ': ' . $language . '; ' . BaseAmosModule::t('amoscore', 'translation') . ': ' . $translation);
            }
        } else {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Language translate not found while updating translation. => languageSourceId') . ': '  . $languageSourceId . '; ' . BaseAmosModule::t('amoscore', 'language') . ': ' . $language . '; ' . BaseAmosModule::t('amoscore', 'translation') . ': ' . $translation . '; ' . BaseAmosModule::t('amoscore', 'Skipping') . '...');
        }
        return $ok;
    }
    
    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        /** @var \lajax\translatemanager\Module $translateManagerModule */
        $translateManagerModule = Yii::$app->getModule('translatemanager');
        if (!isset($translateManagerModule)) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'translatemanager module not present. Skip removing translations.'));
            return true;
        }
        
        $ok = $this->beforeRemoveConfs();
        if ($ok) {
            $ok = $this->removeAllTranslations();
        }
        if ($ok) {
            $ok = $this->afterRemoveConfs();
        }
        
        return $ok;
    }
    
    /**
     * Override this to make operations before removing the translations configurations.
     * @return bool
     */
    protected function beforeRemoveConfs()
    {
        return true;
    }
    
    /**
     * Override this to make operations after removing the translations configurations.
     * @return bool
     */
    protected function afterRemoveConfs()
    {
        return true;
    }
    
    /**
     * This method add all translations configurations set in the global array. It verify if a configuration is already present.
     * If the configuration not exists the method create it, otherwise it goes over.
     * @return  boolean
     */
    private function removeAllTranslations()
    {
        $ok = true;
        foreach ($this->translations as $language => $translationConfs) {
            $ok = $this->removeLanguageTranslations($language, $translationConfs);
            if (!$ok) {
                break;
            }
        }
        return $ok;
    }
    
    /**
     * Method to remove all translations.
     * @param string $language The general language of the configurations.
     * @param array $translationConfs Key => value array that contains a translations configuration.
     */
    private function removeLanguageTranslations($language, $translationConfs)
    {
        $ok = true;
        foreach ($translationConfs as $translationConf) {
            if (isset($translationConf['language'])) {
                $language = $translationConf['language'];
            }
            if (!$this->languageExists($language)) {
                return false;
            }
            $ok = $this->removeLanguageTranslation($language, $translationConf);
            if (!$ok) {
                break;
            }
        }
        return $ok;
    }
    
    /**
     * Method useful to remove a single translation configuration.
     * @param string $language The language of the configurations.
     * @param array $translationConf Key => value array that contains a translations configuration.
     * @return bool
     */
    private function removeLanguageTranslation($language, $translationConf)
    {
        $languageSource = $this->findLanguageSource($translationConf['category'], $translationConf['source']);
        if (is_null($languageSource)) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Language source not found while removing translation. => category') . ': ' .$translationConf['category'] . '; ' . BaseAmosModule::t('amoscore', 'message') . ': ' . $translationConf['source']);
            return false;
        }
        if (isset($translationConf['update']) && $translationConf['update']) {
            $ok = $this->restoreLanguageTranslate($languageSource->id, $language, $translationConf);
        } else {
            $ok = $this->removeLanguageTranslate($languageSource->id, $language);
            if (isset($translationConf['removeSource']) && ($translationConf['removeSource'] === true)) {
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Source to remove. => category') . ': ' .$translationConf['category'] . '; ' . BaseAmosModule::t('amoscore', 'message') . ': ' . $translationConf['source']);
                if ($ok) {
                    $ok = $this->removeLanguageSource($languageSource);
                }
            } else {
                MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Source not to remove. => category') . ': ' .$translationConf['category'] . '; ' . BaseAmosModule::t('amoscore', 'message') . ': ' . $translationConf['source']);
            }
        }
        return $ok;
    }
    
    /**
     * This method remove a language translation.
     * @param int $languageSourceId The language source id.
     * @param string $language The general language of the configurations.
     * @return bool
     */
    private function removeLanguageTranslate($languageSourceId, $language)
    {
        $languageTranslate = $this->findLanguageTranslate($languageSourceId, $language);
        if (is_null($languageTranslate)) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Language translate not found while removing translation. => languageSourceId') . ': '  . $languageSourceId . '; ' . BaseAmosModule::t('amoscore', 'language') . ': ' . $language);
            return false;
        }
        $ok = $languageTranslate->delete();
        if ($ok) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Translation successfully removed. => languageSourceId') . ': '  . $languageSourceId . '; ' . BaseAmosModule::t('amoscore', 'language') . ': ' . $language);
        } else {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Error while removing language translate. => languageSourceId') . ': '  . $languageSourceId . '; ' . BaseAmosModule::t('amoscore', 'language') . ': ' . $language);
        }
        return $ok;
    }
    
    /**
     * This method restore the previous translation.
     * @param int $languageSourceId The language source id.
     * @param string $language The general language of the configurations.
     * @param array $translationConf Key => value array that contains a translations configuration.
     * @return bool
     */
    private function restoreLanguageTranslate($languageSourceId, $language, $translationConf)
    {
        $languageTranslate = $this->findLanguageTranslate($languageSourceId, $language);
        if (is_null($languageTranslate)) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Language translate not found while restoring translation. => languageSourceId') . ': '  . $languageSourceId . '; ' . BaseAmosModule::t('amoscore', 'language') . ': ' . $language . '; ' . BaseAmosModule::t('amoscore', 'Skipping') . '...');
            return true;
        }
        $languageTranslate->translation = $translationConf['oldTranslation'];
        $ok = $languageTranslate->save();
        if ($ok) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Translation successfully restored. => languageSourceId') . ': '  . $languageSourceId . '; ' . BaseAmosModule::t('amoscore', 'language') . ': ' . $language);
        } else {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Error while restoring language translate. => languageSourceId') . ': '  . $languageSourceId . '; ' . BaseAmosModule::t('amoscore', 'language') . ': ' . $language);
        }
        return $ok;
    }
    
    /**
     * This method remove a language source.
     * @param LanguageSource $languageSource The language source object.
     * @return bool
     */
    private function removeLanguageSource($languageSource)
    {
        if (is_null($languageSource)) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Language source not found while removing translation.  => category') . ': ' .$languageSource->category . '; ' . BaseAmosModule::t('amoscore', 'message') . ': ' . $languageSource->message);
            return false;
        }
        $ok = $languageSource->delete();
        if (!$ok) {
            MigrationCommon::printConsoleMessage(BaseAmosModule::t('amoscore', 'Error while removing language source. => category') . ': ' .$languageSource->category . '; ' . BaseAmosModule::t('amoscore', 'message') . ': ' . $languageSource->message);
        }
        return $ok;
    }
}
