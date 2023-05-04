<?php

namespace open20\amos\core\components;

use creocoder\flysystem\Filesystem;
use League\Flysystem\FileExistsException;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\FileHelper;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\helpers\Url;

/**
 * Custom class for asset manager with CDN (AWS S3)
 * ```php
 * 'assetManager' => [
 *      'class'    => \open20\amos\core\components\CloudAssetManager::class,
 * 	'forceCopy' => false,
 *      'cache'    => 'assetscache', // Name of your cache component
 *      'baseUrlS3'  => 'https://DOMAIN.cloudfront.net/FOLDERCUSTOM', 
 *      'filesystem' => [
 *          'class'  => \creocoder\flysystem\AwsS3Filesystem::class, // For AWS S3 or S3-compatible
 *          'key' => 'KEY_AWS_S3',
 *          'secret' => 'SECRET_AWS_S3',
 *          'region' => 'eu-central-1',//or other
 *          'prefix' => 'FOLDERCUSTOM',//some folder in the baseUrlS3
 *          'bucket' => 'BUCKET',
 *      ],
 * ],
 * ```
 */
class CloudAssetManager extends AmosAssetManager {

    const CACHE_META_KEY = 'cloud-assets-meta-%s';

    /**
     * @var array
     */
    private static $allValidCss = [];

    /**
     * If set to true it disables the use of the cache to avoid checking the 
     * assets but uses the file instead
     * @var bool
     */
    public $useFileForCompleted = true;

    /**
     * @var string
     */
    public $baseUrlS3;

    /**
     * @var Filesystem
     */
    public $filesystem;

    /**
     * Initializes the component.
     * @throws \yii\base\InvalidConfigException
     */
    public function init() {
        parent::init();

        if (empty($this->basePath)) {
            throw new InvalidConfigException('Relative path at the destination must be defined.');
        }

        $this->filesystem = Instance::ensure($this->filesystem, Filesystem::class);

        if (!empty($this->filesystem->cache)) {
            throw new InvalidConfigException('You should not use League\Flysystem\Cached\CachedAdapter due to its inefficiency. This extension has its own caching system.');
        }
    }

    protected function publishDirectory($src, $options) {
        $dir = $this->hash($src);
        $dstDir = $this->basePath . DIRECTORY_SEPARATOR . $dir;
        if ($this->linkAssets) {
            if (!is_dir($dstDir)) {
                FileHelper::createDirectory(dirname($dstDir), $this->dirMode, true);
                try { // fix #6226 symlinking multi threaded
                    symlink($src, $dstDir);
                } catch (\Exception $e) {
                    if (!is_dir($dstDir)) {
                        throw $e;
                    }
                }
            }
        } elseif (!empty($options['forceCopy']) || ($this->forceCopy && !isset($options['forceCopy'])) || !is_dir($dstDir)) {
            $opts = array_merge(
                    $options,
                    [
                        'dirMode' => $this->dirMode,
                        'fileMode' => $this->fileMode,
                        'copyEmptyDirectories' => false,
                    ]
            );
            if (!isset($opts['beforeCopy'])) {
                if ($this->beforeCopy !== null) {
                    $opts['beforeCopy'] = $this->beforeCopy;
                } else {
                    $opts['beforeCopy'] = function ($from, $to) {
                        return strncmp(basename($from), '.', 1) !== 0;
                    };
                }
            }
            if (!isset($opts['afterCopy']) && $this->afterCopy !== null) {
                $opts['afterCopy'] = $this->afterCopy;
            }

            FileHelper::copyDirectory($src, $dstDir, $opts);
        }

        return $this->publishDirectoryS3($dstDir, $options);
    }

    /**
     * 
     * @param type $name
     * @param type $config
     * @param type $publish
     * @return \open20\amos\core\components\AssetBundle
     */
    protected function loadBundle($name, $config = [], $publish = true) {
        if (!isset($config['class'])) {
            $config['class'] = $name;
        }
        /* @var $bundle AssetBundle */
        $bundle = \Yii::createObject($config);

        $allValidCss = [
            'scss/main-agid.scss',
            'scss/main-design.scss',
            'less/main.less',
            'less/main-bi.less',
            'less/favorites.less'
        ];
        foreach ($allValidCss as $validCss){
            if(!in_array($validCss, self::$allValidCss )){
                self::$allValidCss[] = $validCss;
            }
        }

        if (!empty($bundle->css)) {
            foreach ($bundle->css as $bcss) {
                self::$allValidCss[] = $bcss;
            }
        }

        if ($publish) {
            $bundle->publish($this);
        }

        return $bundle;
    }

    /**
     * @param string $src
     * @param array $options
     * @return string[]
     */
    protected function publishDirectoryS3($src, $options) {
        $dir = \yii\helpers\StringHelper::basename($src);
        $dstDir = $this->baseUrl . DIRECTORY_SEPARATOR . $dir;

        $baseAsset = \Yii::getAlias('@webroot' . $dstDir);

        $finalKey = $this->getMetaKey($dir . '-completed');

        if ($this->useFileForCompleted == true) {
            $completed = $this->getCompleted($finalKey);
        } else {
            $this->cache->get($finalKey);
        }

        if (!empty($options['forceCopy']) || ($this->forceCopy && !isset($options['forceCopy'])) || !$completed) {
            $currentLength = strlen($src);

            $filesConvert = FileHelper::findFiles($src, array_merge($this->filterFilesOptions, $options));

            foreach ($filesConvert as $fileC) {
                $dstFileC = substr($fileC, $currentLength);
                $converter = $this->getConverter();
                if (!empty($converter) && in_array(trim($dstFileC, DIRECTORY_SEPARATOR), self::$allValidCss)) {
                    $converter->convert(trim($dstFileC, DIRECTORY_SEPARATOR), $baseAsset);
                }
            }

            $directories = FileHelper::findDirectories($src, array_merge($this->filterFilesOptions, $options));

            $files = FileHelper::findFiles($src, array_merge($this->filterFilesOptions, $options));

            $meta = $this->getMetaFromRemoteData($this->filesystem->listContents($dstDir, true), $dir);

            if (isset($options['beforeCopy'])) {
                $beforeCopy = $options['beforeCopy'];
            } elseif ($this->beforeCopy !== null) {
                $beforeCopy = $this->beforeCopy;
            }
            if (isset($options['afterCopy'])) {
                $afterCopy = $options['afterCopy'];
            } elseif ($this->afterCopy !== null) {
                $afterCopy = $this->afterCopy;
            }

            foreach ($directories as $directory) {
                $dstDirectory = substr($directory, $currentLength);

                if (isset($beforeCopy) && !$beforeCopy($directory, $dstDir . $dstDirectory)) {
                    continue;
                }

                $key = $this->getMetaKey($dir . $dstDirectory);
                $dirMeta = isset($meta[$key]) ? $meta[$key] : null;
                if (!isset($dirMeta)) {
                    $this->filesystem->createDir($dstDir . $dstDirectory);
                }
            }

            foreach ($files as $file) {

                $dstFile = substr($file, $currentLength);

                $dstBaseFile = basename($dstFile);

                if (isset($beforeCopy) && !$beforeCopy($file, $dstDir . $dstFile)) {
                    continue;
                }

                $key = $this->getMetaKey(dirname($dir . $dstFile));
                $dirMeta = isset($meta[$key]) ? $meta[$key] : null;

                try {
                    if (!isset($dirMeta[$dstBaseFile])) {
                        $this->filesystem->writeStream($dstDir . $dstFile, fopen($file, 'r'));

                        if (isset($afterCopy)) {
                            $afterCopy($file, $dstDir . $dstFile);
                        }
                    }
                } catch (FileExistsException $e) {
                    // Do nothing
                }
            }

            if ($this->useFileForCompleted == true) {
                $this->setCompleted($finalKey);
            } else {
                $this->cache->set($finalKey, true, 0);
            }
            $this->deleteOldAssets();
        }


        return [$dstDir, $this->baseUrlS3 . $this->baseUrl . '/' . $dir];
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
            try {
                if (!$completed) {
                    $methodOld = true;
                    if (method_exists($this->filesystem, 'removeDirectory')) {
                        $methodOld = false;
                    }
                    $dirsS3 = $this->filesystem->listContents($this->baseUrl, false);
                    foreach ($dirsS3 as $dirS3) {
                        if (!empty($dirS3['basename']) && !empty($dirS3['path']) && !empty($dirS3['type']) && $dirS3['type'] == 'dir') {
                            if (strpos($dirS3['basename'], $prefix) === false) {
                                if ($methodOld) {
                                    $this->filesystem->deleteDir($dirS3['path']);
                                } else {
                                    $this->filesystem->deleteDirectory($dirS3['path']);
                                }
                            }
                        }
                    }

                    $dirs = FileHelper::findDirectories($url, ['recursive' => false]);

                    $files = FileHelper::findFiles($url, ['recursive' => false]);

                    foreach ($dirs as $dir) {
                        if (strpos($dir, $prefix) === false) {
                            FileHelper::removeDirectory($dir);
                        }
                    }

                    foreach ($files as $file) {
                        if (strpos($file, $prefix) === false) {
                            FileHelper::unlink($file);
                        }
                    }
                    
                    $this->cache->set($finalKey, true, 0);
                }
            } catch (Exception $ex) {
                
            }
        } catch (\Exception $ex) {
            
        }
    }

    /**
     * @param array $data
     * @param string $hash
     * @return array
     */
    private function getMetaFromRemoteData(array $data, $hash) {
        $meta = [];
        $key = $this->getMetaKey($hash);
        $meta[$key] = [];

        foreach ($data as $item) {
            if ($item['type'] === 'file') {
                $key = $this->getMetaKey(substr($item['dirname'], strlen($this->basePath) + 1));
                $meta[$key][$item['basename']] = 1;
            }
        }

        return $meta;
    }

    /**
     * @param string $dir
     * @return string
     */
    private function getMetaKey($dir) {
        return rtrim(sprintf(self::CACHE_META_KEY, $dir), '-/');
    }

    public function setCompleted($finalKey) {
        $path = $this->getPathCompleted($finalKey);
        $h = fopen($path, 'w');
        fwrite($h, date('Y-m-dHis'));
        fclose($h);
    }

    /**
     * 
     * @param type $finalKey
     * @return boolean
     */
    public function getCompleted($finalKey) {
        $path = $this->getPathCompleted($finalKey);
        if (file_exists($path)) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param type $finalKey
     * @return type
     */
    protected function getPathCompleted($finalKey) {
        return realpath(\Yii::getAlias('@webroot/assets')) . DIRECTORY_SEPARATOR . $finalKey . '.pid';
    }

}
