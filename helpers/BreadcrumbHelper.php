<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\helpers
 * @category   CategoryName
 */

namespace lispa\amos\core\helpers;

use lispa\amos\core\controllers\BaseController;
use yii\base\Component;
use yii\base\Exception;
use yii\widgets\Breadcrumbs;
use yii\helpers\StringHelper;
use yii\base\Event;
use yii\helpers\Url;


/**
 * Class BreadcrumbHelper
 * @package lispa\amos\core\helpers
 */
class BreadcrumbHelper extends Component
{
    private  static $blacklist = [
       'page',
        'perPage',
        'enableSearch',
        'orderAttribute',
        'orderType',
        'id'
    ];

    private static $action_blacklist = [
        'create','update'
    ];

    private static $controller_whitelist = [
        'join', 'user-profile'
    ];

    private static $action_whitelist = [
        'partecipa'
    ];

    public static $stack = [];

    private static $keys = [];

    private static $reload = false;


    const EVENT_CRUMB_DELETED = 'crumbDeleted';
    const EVENT_CRUMB_ADDED = 'crumbAdded';

    /**
     * @return null|object
     */
    public static function getCache()
    {
        $cache =  \Yii::$app->get('breadcrumbcache', false);
        if(!$cache){
            $cache = \Yii::$app->getCache();
        }
        return $cache;
    }

    /**
     * return hashed key for cache
     *
     * @return string
     */
    private static function key()
    {
        return md5(self::getUserId() . '_' . \Yii::$app->session->getId() );
    }

    /**
     * return current logged user id
     *
     * @return mixed
     */
    public static function getUserId()
    {
        $userId = '';

        try {
            if (\Yii::$app->user) {
                if(!\Yii::$app->user->isGuest) {
                    $userId = \Yii::$app->user->identity->id;
                }
            }
        }catch (Exception $ex){
            Yii::getLogger()->log($ex->getMessage(), \yii\log\Logger::LEVEL_ERROR);
        }

        return $userId;
    }

    /**
     * reset cache and trail
     */
    public static function reset()
    {
        self::clearCache();

    }

    /**
     * reset cache
     */
    public static function clearCache()
    {
        try {
            $cache = self::getCache();
            if(!empty($cache)) {
                $cache->delete(self::key());
            }
        }catch(Exception $ex){
            Yii::getLogger()->log($ex->getMessage(), \yii\log\Logger::LEVEL_ERROR);
        }
    }

    /**
     * create the crumb and send it to the trail
     *
     * @param $label
     * @param null $url
     * @param null $template
     */
    public static function add($label, $url = null, $template = null, $remove_action = null)
    {
       $request = \Yii::$app->request;

        $label = StringHelper::truncateWords($label,10,'...');

        if (!$request->isPjax && $url != '/site/error') {
            if (empty($url)) {
                $url = $request->url;
            }
            $crumb = self::createCrumb(compact('label', 'url', 'template','remove_action'));
            self::addToTrail($crumb);
            if(self::$reload){
                self::$reload = false;
                \Yii::$app->response->redirect($crumb->url);
            }
        }
    }


    /**
     * add crumb to the trail
     *
     * @param $crumb
     * @param $position
     * @param $index
     */
    public static function addToTrail($crumb)
    {
        $trail = self::getCache()->get(self::key());
        if (self::check($crumb,$trail)) {
            $trail[] = $crumb;
            \Yii::$app->trigger(self::EVENT_CRUMB_ADDED, new Event(['sender' => ['data' => $crumb]]));
            self::clearCache();
            self::getCache()->set(self::key(), $trail);
        }
    }
    
    /**
     * 
     */
    public static function lastCrumbUrl()
    {
        $url = '';
         $trail = self::getCache()->get(self::key());
         $crumb = end($trail);
         while(!is_null($crumb))
         {
             if ($crumb->visible == true) {
                $url = $crumb->url;
                break;
             }
             $crumb = prev($trail);
         }
         return $url;
    }
    
    /**
     * check if $crumb is already the last element of the trail
     *
     * @param $crumb
     * @return bool
     */
    public static function check($crumb, $trail)
    {
        if ($trail && sizeof($trail))
        {
            self::isInBlackList($crumb, $trail);
            $i=1;
            /**var Crumb $value*/
            foreach ($trail as $key => $value)
            {
                if ($value->equals($crumb))
                {
                    if(sizeof($trail) == $i) {
                        return false;
                    }
                    else {
                        self::clearCache();
                        $removed_crumbs = array_splice($trail, $i);
                        foreach($removed_crumbs as $z => $crumb) {
                            \Yii::$app->trigger(self::EVENT_CRUMB_DELETED, new Event(['sender' => ['data' => $crumb]]));
                            if(!is_null($crumb->remove_action)){
                                try {
                                    \Yii::$app->runAction ($crumb->remove_action);
                                    self::$reload = true;
                                }catch(\Exception $ex) {

                                }
                            }
                        }

                        $last_crumb = end($trail);
                        if($last_crumb && count($trail) > 1)
                        {
                            $params = $last_crumb->params;
                            if(sizeof($params))
                            {
                                foreach($params as $key_param => $value_param)
                                {
                                    static::$keys[] = $key_param;
                                    if(is_array($value_param)) {
                                        static::$keys = array_merge(static::$keys, self::getKeysMultidimensional($value_param));
                                    }
                                }
                                foreach(static::$blacklist as $banned) {
                                    if(in_array($banned,static::$keys) && !in_array($last_crumb->controller,static::$controller_whitelist)
                                        && !in_array($last_crumb->controller,static::$action_whitelist)) {
                                        /** @var Crumb $last_crumb */
                                        $last_crumb->visible = false;
                                        break;
                                    }
                                }
                            }
                            if(in_array($last_crumb->action,static::$action_blacklist))
                            {
                                $last_crumb->visible = false;
                            }
                        }
                        self::write($trail);
                    }
                    return false;
                }
                $i++;

            }
            return true;
        } else {
            return true;
        }
    }


    public static function write($trail)
    {
        self::getCache()->set(self::key(), $trail);
    }

    /**
     *  it passes the trail to the core breadcrumb widget
     */
    public static function renderCrumbs()
    {
        $data = [];
        $trail = self::getCache()->get(self::key());
        if($trail) {
            /** @var Crumb $crumb */
            foreach ($trail as $crumb) {
                if ($crumb->visible == true) {
                    $data[] = $crumb->asArray();
                }
            }
        }
        return $data;
    }

    /**
     * @param array $array
     * @return array
     */
    public static function getKeysMultidimensional(array $array)
    {
        $keys = array();
        foreach($array as $key => $value) {
            $keys[] = $key;
            if( is_array($value) ) {
                $keys = array_merge($keys, self::getKeysMultidimensional($value));
            }
        }
        return $keys;
    }

    /**
     * @param null $crumb
     * @return null
     */
    public static function createCrumb($array_url = null)
    {
        $title = '';
        $name_class = '';

        list($controller, $actionID) = \Yii::$app->createController($array_url['url']);
        $controller = \Yii::$app->controller;
        if ($controller instanceof BaseController)
        {
            $model = $controller->model;
            $title = $model->__toString();
            $name_class = $controller->model->className();
        }

        $crumb = new Crumb();
        $crumb->label = $array_url['label'] . " " . $title;
        $crumb->url = $array_url['url'];
        $crumb->controller = $controller->id;
        $crumb->model = $name_class;
        $crumb->module = $controller->module->className();
        $action_name = explode ('?',$actionID);
        $crumb->action = reset($action_name);
        $crumb->route = $controller->route;
        $crumb->remove_action = isset($array_url['remove_action']) ?  $array_url['remove_action'] : null;
        $crumb->visible = true;

        if( isset($controller->actionParams) && sizeof($controller->actionParams) ) {
            $crumb->params = $controller->actionParams;
        }

        return $crumb;
    }


    /**
     * @param Crumb $crumb
     * @return bool
     */
    private static function isInBlackList(Crumb $crumb, $trail)
    {
        $ret = false;

        $params = $crumb->params;
        if(sizeof($params))
        {
            foreach($params as $key_param => $value_param)
            {
                static::$keys[] = $key_param;
                if(is_array($value_param)) {
                    static::$keys = array_merge(static::$keys, self::getKeysMultidimensional($value_param));
                }
            }
            foreach(static::$blacklist as $banned) {
                if(in_array($banned,static::$keys) && !in_array($crumb->controller,static::$controller_whitelist)
                    && !in_array($crumb->controller,static::$action_whitelist)) {
                    /** @var Crumb $last_crumb */
                    $last_crumb = end($trail);
                    if($last_crumb)
                    {
                        if($last_crumb->controller != $crumb->controller)
                        {
                            $last_crumb->visible = true;
                        }
                    }
                    $crumb->visible = false;
                    $ret = true;
                    break;
                }
            }
        }
        if(in_array($crumb->action,static::$action_blacklist))
        {
            /** @var Crumb $last_crumb */
            $last_crumb = end($trail);
            if($last_crumb)
            {
                if($last_crumb->controller != $crumb->controller)
                {
                    $last_crumb->visible = true;
                }
            }
            $crumb->visible = false;
            $et = true;
        }
        return $ret;
    }
}