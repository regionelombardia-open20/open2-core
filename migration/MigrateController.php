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

use Yii;
use yii\console\controllers\MigrateController as YiiMigrateController;
use yii\helpers\ArrayHelper;

/**
 * Class MigrateController
 * @package open20\amos\core\migration
 */
class MigrateController extends YiiMigrateController
{
    /**
     * @var array
     */
    public $migrationLookup = [];

    /**
     * @var array
     */
    private $_migrationFiles;

    /**
     * @var array $migrationNameSpacesPaths
     */
    private $migrationNameSpacesPaths = [];

    /**
     * @return array|null
     */
    protected function getMigrationFiles()
    {
        if ($this->_migrationFiles === null) {
            $this->_migrationFiles = [];
            if (is_array($this->migrationPath)) {
                $array_migrationPath = $this->migrationPath;
            } else {
                $array_migrationPath = [$this->migrationPath];
            }
            foreach ($this->migrationNamespaces as $namespace) {
                $nameSpacePath = $this->getNamespacePath($namespace);
                $array_migrationPath[] = $nameSpacePath;
                $this->migrationNameSpacesPaths[$nameSpacePath] = $namespace;
            }

            $directories = array_merge($this->migrationLookup, $array_migrationPath);
            $extraPath = ArrayHelper::getValue(Yii::$app->params, 'yii.migrations');
            if (!empty($extraPath)) {
                $directories = array_merge((array)$extraPath, $directories);
            }

            foreach (array_unique($directories) as $dir) {
                $dir = Yii::getAlias($dir, false);
                if ($dir && is_dir($dir)) {
                    $handle = opendir($dir);
                    while (($file = readdir($handle)) !== false) {
                        if ($file === '.' || $file === '..') {
                            continue;
                        }
                        $path = $dir . DIRECTORY_SEPARATOR . $file;
                        if (preg_match('/^(m(\d{6}_\d{6})_.*?)\.php$/', $file, $matches) && is_file($path)) {
                            $this->_migrationFiles[$matches[1]] = $path;
                        }
                    }
                    closedir($handle);
                }
            }

            ksort($this->_migrationFiles);
        }

        return $this->_migrationFiles;
    }

    /**
     * Returns the file path matching the give namespace.
     * @param string $namespace namespace.
     * @return string file path.
     * @since 2.0.10
     */
    private function getNamespacePath($namespace)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, Yii::getAlias('@' . str_replace('\\', '/', $namespace)));
    }

    /**
     * @inheritdoc
     */
    protected function createMigration($class)
    {
        $file = $this->getMigrationFiles()[$class];
        $pathParts = pathinfo($file);
        $path = $pathParts['dirname'];
        require_once($file);

        if (isset($this->migrationNameSpacesPaths[$path])) {
            $classToInstance = $this->migrationNameSpacesPaths[$path] . '\\' . $class;
            return new $classToInstance(['db' => $this->db]);
        } else {
            return new $class(['db' => $this->db]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function getNewMigrations()
    {
        $applied = [];
        foreach ($this->getMigrationHistory(null) as $version => $time) {
            $applied[substr($version, 1, 13)] = true;
        }

        $migrations = [];
        foreach ($this->getMigrationFiles() as $version => $path) {
            if (!isset($applied[substr($version, 1, 13)])) {
                $migrations[] = $version;
            }
        }

        return $migrations;
    }
}
