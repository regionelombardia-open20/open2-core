<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core
 * @category   CategoryName
 */

namespace open20\amos\core\giiamos;

use yii\gii\Generator as BaseGenerator;
use yii\helpers\StringHelper;
use Yii;

abstract class Generator extends BaseGenerator {

    public $vendorPath;
    public $modelClass;

    public function init() {
        parent::init();
    }

    public function rules() {
        return array_merge(parent::rules(), [['vendorPath', 'string']]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return array_merge(
                parent::attributeLabels(), [
            'vendorPath' => 'Percorso relativo dentro la vendor',
                ]
        );
    }

    /**
     * @inheritdoc
     */
    public function hints() {
        return array_merge(
                parent::hints(), [
            'vendorPath' => 'Percorso relativo dentro la vendor  per esempio "open20/amos-news/src/widgets"',
                ]
        );
    }

    public function validateNamespace($attribute) {
        $value = $this->$attribute;
        $value = ltrim($value, '\\');
        $path = Yii::getAlias('@' . str_replace('\\', '/', $value), false);


        if (!empty($this->vendorPath)) {
            $path = $this->createPathFromNamespace();
        }

        if (empty($path) || $path == false) {
            $this->addError($attribute, 'Namespace must be associated with an existing directory.');
        }
    }

    /**
     * An inline validator that checks if the attribute value refers to a valid namespaced class name.
     * The validator will check if the directory containing the new class file exist or not.
     * @param string $attribute the attribute being validated
     * @param array $params the validation options
     */
    public function validateNewClass($attribute, $params) {
        $class = ltrim($this->$attribute, '\\');
        if (($pos = strrpos($class, '\\')) === false) {
            $this->addError($attribute, "The class name must contain fully qualified namespace name.");
        } else {
            $ns = substr($class, 0, $pos);
            $path = Yii::getAlias('@' . str_replace('\\', '/', $ns), false);
            if (!empty($this->vendorPath)) {
                $path = $this->createPathFromNamespace();
            } else if (empty($path) && !empty($this->modelClass)) {
                $isSearch = (strpos($class, 'search') === false) ? false : true;
                $path = $this->createPathFromNs($ns, $isSearch, true, $isSearch);
            }
            if (empty($path) || $path == false) {
                $this->addError($attribute, "The class namespace is invalid: $ns");
            } elseif (!is_dir($path)) {
                $this->addError($attribute, "Please make sure the directory containing this class exists: $path");
            }
        }
    }

    /**
     *
     * @param boolean $migration
     * @return string|null
     */
    public function createPathFromNamespace($migration = false) {
        $path = null;
        try {
            $basePath = Yii::getAlias('@' . str_replace('\\', '/', "vendor"), false);
            if ($migration == true) {
                $path = $basePath;
                $search = StringHelper::baseName($this->vendorPath);
                $pathMigration = str_replace([$search, '/' . $search, '\\' . $search], '', $this->vendorPath);
                $path = $basePath . DIRECTORY_SEPARATOR . $pathMigration . 'migrations';
            } else {
                $path = $basePath . DIRECTORY_SEPARATOR . $this->vendorPath;
            }
        } catch (\Exception $e) {
            
        }
        return $path;
    }

    /**
     *
     * @param type $namespace
     * @return string
     */
    public function createPathFromNs($namespace, $isSearch = false, $notUseExtensions = false, $isSearchValidation = false) {
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
                    $search3 = '/' . StringHelper::baseName($pathInVendor);
                    if ($isSearch == true) {
                        if ($isSearchValidation == true) {
                            $path = str_replace($search3, '', $pathInVendor) . DIRECTORY_SEPARATOR . $class;
                        } else {
                            $path = str_replace(StringHelper::baseName($pathInVendor), '', $pathInVendor) . 'search' . DIRECTORY_SEPARATOR . $class;
                        }
                    } else {
                        if ($notUseExtensions == true) {
                            $path = str_replace([$search, $search2], '', $pathInVendor) . $class;
                        } else {
                            $path = str_replace([$search, $search2], '', $pathInVendor) . $class . '.php';
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            
        }
        return $path;
    }

}
