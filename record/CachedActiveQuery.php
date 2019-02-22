<?php
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    amos-basic-template
 * @category   CategoryName
 */

namespace lispa\amos\core\record;

use yii\db\ActiveQuery;

class CachedActiveQuery extends ActiveQuery
{
    private $cacheDuration = null;
    private $functionName  = '';
    private $q             = null;

    /**
     * The cache object or the ID of the cache application component that is used for query caching
     * @var type
     */
    static $queryCache = 'cache';

    /**
     * Use query caching for this ActiveQuery.
     *
     * @param int|null $duration Seconds to cache; Use 0 to indicate that the cached data will never expire; NULL indicates No Cache.
     * @return lispa\amos\core\record\CachedActiveQuery
     */
    public function cache($duration = null, $queryCache = null)
    {
        if (!is_null($duration) && $duration >= 0) {
            $this->cacheDuration = $duration;
        }
        if (!is_null($queryCache)) {
            self::$queryCache = $queryCache;
        }
        return $this;
    }

    public function all($db = null)
    {
        return $this->run('all', null, $db);
    }

    public function one($db = null)
    {
        return $this->run('one', null, $db);
    }

    public function count($q = '*', $db = null)
    {
        return $this->run('count', $q, $db);
    }

    public function sum($q, $db = null)
    {
        return $this->run('sum', $q, $db);
    }

    public function max($q, $db = null)
    {
        return $this->run('max', $q, $db);
    }

    public function min($q, $db = null)
    {
        return $this->run('min', $q, $db);
    }

    public function average($q, $db = null)
    {
        return $this->run('average', $q, $db);
    }

    public function scalar($db = null)
    {
        return $this->run('scalar', null, $db);
    }

    public function column($db = null)
    {
        return $this->run('column', null, $db);
    }

    public function exists($db = null)
    {
        return $this->run('exists', null, $db);
    }

    protected function run($functionName, $q, $db = null)
    {
        $this->functionName = $functionName;
        $this->q            = $q;
        if (is_null($db)) {
            $modelClassName = $this->modelClass;
            $db             = $modelClassName::getDb();
        }
        $db->enableQueryCache = true;
        $db->queryCache       = self::$queryCache;

        if (!is_null($this->cacheDuration)) {
            return $db->cache(function(\yii\db\Connection $db) {
                    $functionName = $this->functionName;
                    if (!is_null($this->q)) {
                        return parent::$functionName($this->q, $db);
                    } else {
                        return parent::$functionName($db);
                    }
                }, $this->cacheDuration);
        } else {
            if (!is_null($this->q)) {
                return parent::$functionName($this->q, $db);
            } else {
                return parent::$functionName($db);
            }
        }
    }

    /**
     * @param ActiveQuery $parentObj
     */
    public function loadFromParentObj(ActiveQuery $parentObj)
    {
        $objValues = get_object_vars($parentObj); // return array of object values
        foreach ($objValues AS $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Create a new instance of CachedActiveQuery from $parentObj
     * @param ActiveQuery $parentObj
     */
    public static function instance(ActiveQuery $parentObj)
    {
        $object = new CachedActiveQuery($parentObj->modelClass);
        $object->loadFromParentObj($parentObj);
        return $object;
    }

    /**
     * reset cache
     * @param string $queryCache
     */
    public static function reset($queryCache = null)
    {
        if(empty($queryCache)){
            $queryCache = self::$queryCache;
        }
        self::clearCache($queryCache);
    }

    /**
     * reset cache
     * @param string $queryCache
     */
    public static function clearCache($queryCache)
    {
        try {
            $cache = self::getCacheComponent($queryCache);
            if (!empty($cache)) {
                $cache->flush();
            }
        } catch (Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), \yii\log\Logger::LEVEL_ERROR);
        }
    }

    /**
     *
     * @param string $queryCache
     * @return null|yii\base\Component
     */
    public static function getCacheComponent($queryCache)
    {
        $cacheComponent = null;
        if (!empty(\Yii::$app->$queryCache)) {
            $cacheComponent = \Yii::$app->$queryCache;
        }
        return $cacheComponent;
    }
}