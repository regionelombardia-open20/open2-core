<?php

namespace open20\amos\core\components;

use yii\base\InvalidArgumentException;
use yii\caching\Cache;
use yii\di\Instance;
use yii\helpers\FileHelper;
use Yii;

/**
 * Custom class for asset manager
 * ```php
 * 'assetManager' => [
 *      'class'    => \open20\amos\core\components\AmosAssetManager::class,
 * 	'forceCopy' => false,
 *      'cache'    => 'assetscache', // Name of your cache component
 * ```
 */
class AmosAssetManager extends \yii\web\AssetManager {

    const CACHE_HASH_KEY = 'cloud-assets-hash-%s';

    /**
     * @var Cache
     */
    public $cache = 'cache';

    /**
     * @var array
     */
    public $filterFilesOptions = [];

    /**
     * @var array published assets
     */
    protected $_published = [];

    /**
     * Initializes the component.
     * @throws \yii\base\InvalidConfigException
     */
    public function init() {
        parent::init();
        $this->beforeCopy = function ($from, $to) {
            return strncmp(basename($from), '.', 1) !== 0;
        };

        //We ignore baseUrl because the file is available only from S3
        //$this->baseUrl = realpath(\Yii::getAlias('@webroot/assets'));

        $this->cache = Instance::ensure($this->cache, Cache::class);
    }

    /**
     * 
     * @param string $path
     * @return type
     */
    protected function hash($path) {
        $prefix = $this->prefixHashS3();
        if (is_callable($this->hashCallback)) {
            return $prefix . call_user_func($this->hashCallback, $path);
        }
        $path = (is_file($path) ? dirname($path) : $path) . filemtime($path);

        return $prefix . sprintf('%x', crc32($path . Yii::getVersion() . '|' . $this->linkAssets));
    }

    /**
     * @param string $path
     * @return string
     */
    protected function prefixHashS3() {

        $key = sprintf(self::CACHE_HASH_KEY, 'assets-prefix-key');

        if (!empty(\Yii::$app->params['prefix-assets'])) {
            return \Yii::$app->params['prefix-assets'] . '_';
        }

        $prefix = $this->cache->get($key);

        if ($prefix === false) {
            $prefix = hash_file('crc32', \Yii::getAlias('@app') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'composer.lock') . '_';

            $this->cache->set($key, $prefix);
        }

        return $prefix;
    }

    /**
     * 
     */
    protected function deleteOldAssets() {
        try {
            $prefix = $this->prefixHashS3();
            $url = \Yii::getAlias('@webroot') . $this->baseUrl;
            $finalKey = md5($url . $prefix) . '-completed';
            $completed = $this->cache->get($finalKey);
            if (!$completed) {
                $dirs = FileHelper::findDirectories($url, ['recursive' => false]);

                foreach ($dirs as $dir) {
                    if (strpos($dir, $prefix) === false) {
                        FileHelper::removeDirectory($dir);
                    }
                }
                $this->cache->set($finalKey, true, 0);
            }
        } catch (\Exception $ex) {
            
        }
    }

    /**
     * Publishes a directory.
     * @param string $src the asset directory to be published
     * @param array $options the options to be applied when publishing a directory.
     * The following options are supported:
     *
     * - only: array, list of patterns that the file paths should match if they want to be copied.
     * - except: array, list of patterns that the files or directories should match if they want to be excluded from being copied.
     * - caseSensitive: boolean, whether patterns specified at "only" or "except" should be case sensitive. Defaults to true.
     * - beforeCopy: callback, a PHP callback that is called before copying each sub-directory or file.
     *   This overrides [[beforeCopy]] if set.
     * - afterCopy: callback, a PHP callback that is called after a sub-directory or file is successfully copied.
     *   This overrides [[afterCopy]] if set.
     * - forceCopy: boolean, whether the directory being published should be copied even if
     *   it is found in the target directory. This option is used only when publishing a directory.
     *   This overrides [[forceCopy]] if set.
     *
     * @return string[] the path directory and the URL that the asset is published as.
     * @throws InvalidArgumentException if the asset to be published does not exist.
     */
    protected function publishDirectory($src, $options) {
        $parent = parent::publishDirectory($src, $options);

        $this->deleteOldAssets();

        return $parent;
    }

}
