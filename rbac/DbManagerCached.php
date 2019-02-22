<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\rbac
 * @category   CategoryName
 */

namespace lispa\amos\core\rbac;

use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\log\Logger;
use lispa\amos\core\record\CachedQuery;
use yii\db\Query;

class DbManagerCached extends AuthManager
{

    /**
     * @var string the ID of the cache application component that is used to cache rbac.
     * Defaults to 'cache' which refers to the primary cache application component.
     */
    public $cache = 'cache';
    /**
     * @var integer Lifetime of cached data in seconds
     */
    public $cacheDuration = 86400;
    /**
     * @var string cache key name
     */
    public $cacheKeyName = 'RbacCached';
    /**
     * @var array php cache
     */
    protected $cachedData = [];
    /**
     * @inheritdoc
     */
    public function checkAccess($userId, $permissionName, $params = []) {
        if (!empty($params)) {
            return parent::checkAccess($userId, $permissionName, $params);
        }
        $cacheKey = 'checkAccess:' . $userId . ':' . $permissionName;
        $cached = $this->getCache($cacheKey);
        if (is_null($cached)) {
            $cached = parent::checkAccess($userId, $permissionName);
            $this->setCache($cacheKey, $cached);
        }
        return $cached;
    }

    /**
     * @inheritdoc
     */
    protected function checkAccessRecursive($user, $itemName, $params, $assignments) {
        $cacheKey = 'checkAccessRecursive:' . $user . ':' . $itemName;
        if (!empty($params)) {
            $cacheKey .= ':' . current($params)->primaryKey;
        }
        $cached = $this->getCache($cacheKey);
        if (is_null($cached)) {
            $cached = parent::checkAccessRecursive($user, $itemName, $params, $assignments);
            $this->setCache($cacheKey, $cached);
        }
        return $cached;
    }

    /**
     * @inheritdoc
     */
    protected function getItem($name) {
        $cacheKey = 'Item:' . $name;
        $cached = $this->getCache($cacheKey);
        if (is_null($cached)) {
            $cached = parent::getItem($name);
            $this->setCache($cacheKey, $cached);
        }
        return $cached;
    }


    /**
     * @inheritdoc
     */
    public function getAssignments($userId) {
        if (empty($userId)) {
            return parent::getAssignments($userId);
        }
        $cacheKey = 'Assignments:' . $userId;
        $cached = $this->getCache($cacheKey);
        if (is_null($cached)) {
            $cached = parent::getAssignments($userId);
            $this->setCache($cacheKey, $cached);
        }
        return $cached;
    }

    /**
     * Set a value in cache
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function setCache($key, $value) {
        if (empty($this->cachedData[$key])) {
            $this->cachedData[$key] = $value;
        }
        return $this->resolveCacheComponent()->set($this->cacheKeyName, $this->cachedData, $this->cacheDuration);
    }
    /**
     * Get cached value
     * @param $key
     * @return mixed
     */
    protected function getCache($key) {
        $cached = ArrayHelper::getValue($this->cachedData, $key);
        if (!isset($cached)) {
            $cacheData = $this->resolveCacheComponent()->get($this->cacheKeyName);
            $cached = $this->cachedData[$key] = ArrayHelper::getValue($cacheData, $key);
        }
        return $cached;
    }
    /**
     * Get cached value
     * @param $key
     * @return mixed
     */
    public function deleteAllCache() {
        return $this->resolveCacheComponent()->delete($this->cacheKeyName);
    }

    /**
     * Returns cache component configured as in cacheId
     * @return Cache
     */
    protected function resolveCacheComponent() {
        $component = null;
        try{
            $component = $this->cache;
        }catch (Exception $ex){
            Yii::getLogger()->log($ex->getMessage(), Logger::LEVEL_ERROR);
        }
        return $component;
    }

    /**
     *
     */
    public function invalidateCache()
    {
        $this->deleteAllCache();
        parent::invalidateCache();
    }
    
    
    /**
     * 
     * @param string $roleName
     * @return array
     */
    public function getParents($roleName){
        $parents = [];
        
        try{
            $query = CachedQuery::instance(new Query);
            $query->cache($this->cacheDuration);
            $parents = $query->select('b.*')
                ->from(['a' => $this->itemChildTable, 'b' => $this->itemTable])
                ->where('{{a}}.[[parent]]={{b}}.[[name]]')
                ->andwhere(['child' => $roleName])
                ->all($this->db);
        }catch(yii\base\Exception $ex){
           Yii::getLogger()->log($ex->getMessage(), Logger::LEVEL_ERROR);
        }
        return $parents;
    }

}