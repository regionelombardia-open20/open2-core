<?php
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    amos-basic-template
 * @category   CategoryName
 */

namespace open20\amos\core\record;

use yii\db\Query;

class CachedQuery extends \yii\db\Query
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
     *
     * @param Connection $db
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all($db = null)
    {
        return $this->run('all', null, $db);
    }

    /**
     *
     * @param Connection $db
     * @return array|bool the first row (in terms of an array) of the query result. False is returned if the query
     * results in nothing.
     */
    public function one($db = null)
    {
        return $this->run('one', null, $db);
    }

    /**
     *
     * @param string $q the COUNT expression. Defaults to '*'.
     * @param Connection $db.
     * @return int|string number of records.
     */
    public function count($q = '*', $db = null)
    {
        return $this->run('count', $q, $db);
    }

    /**
     *
     * @param string $q the column name or expression.
     * @param Connection $db
     * @return mixed the sum of the specified column values.
     */
    public function sum($q, $db = null)
    {
        return $this->run('sum', $q, $db);
    }

    /**
     *
     * @param string $q the column name or expression.
     * @param Connection $db
     * @return mixed the average of the specified column values.
     */
    public function max($q, $db = null)
    {
        return $this->run('max', $q, $db);
    }

    /**
     *
     * @param string $q the column name or expression.
     * @param Connection $db
     * @return mixed the maximum of the specified column values.
     */
    public function min($q, $db = null)
    {
        return $this->run('min', $q, $db);
    }

    /**
     *
     * @param string $q the column name or expression.
     * @param Connection $db
     * @return mixed the average of the specified column values.
     */
    public function average($q, $db = null)
    {
        return $this->run('average', $q, $db);
    }

    /**
     *
     * @param Connection $db
     * @return tring|null|false the value of the first column in the first row of the query result.
     * False is returned if the query result is empty.
     */
    public function scalar($db = null)
    {
        return $this->run('scalar', null, $db);
    }

    /**
     *
     * @param Connection $db
     * @return array the first column of the query result. An empty array is returned if the query results in nothing.
     */
    public function column($db = null)
    {
        return $this->run('column', null, $db);
    }

    /**
     *
     * @param Connection $db
     * @return bool whether the query result contains any row of data.
     */
    public function exists($db = null)
    {
        return $this->run('exists', null, $db);
    }

    /**
     *
     * @param string $functionName
     * @param string $q the column name or expression.
     * @param Connection $db
     * @return mixed
     */
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
     * @param Query $parentObj
     */
    public function loadFromParentObj(Query $parentObj)
    {
        $objValues = get_object_vars($parentObj); // return array of object values
        foreach ($objValues AS $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Create a new instance of CachedQuery from $parentObj
     * @param Query $parentObj
     */
    public static function instance(Query $parentObj)
    {
        if (isset($parentObj->modelClass)){
            $object = new CachedQuery($parentObj->modelClass);
        }
        else {
            $object = new CachedQuery();
        }

        $object->loadFromParentObj($parentObj);
        return $object;
    }
    /* Use query caching for this Query.
     *
     * @param int|null $duration Seconds to cache; Use 0 to indicate that the cached data will never expire; NULL indicates No Cache.
     * @return open20\amos\core\record\CachedQuery
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