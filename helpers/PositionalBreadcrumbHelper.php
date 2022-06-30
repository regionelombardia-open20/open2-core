<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\helpers
 * @category   CategoryName
 */

namespace open20\amos\core\helpers;

use open20\amos\core\controllers\BaseController;
use yii\base\Component;
use yii\base\Event;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use yii\log\Logger;

/**
 * Class BreadcrumbHelper
 * @package open20\amos\core\helpers
 */
class PositionalBreadcrumbHelper extends Component
{
    const TYPE_BREAD_DETAIL = 1;
    const TYPE_BREAD_INDEX = 2;
    const TYPE_BREAD_COMMUNITY = 3;

    private static $blacklist = [
        'page',
        'perPage',
        'enableSearch',
        'orderAttribute',
        'orderType',
        'id'
    ];

    private static $action_blacklist = [
        'create', 'update'
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
        $cache = \Yii::$app->get('breadcrumbcache', false);
        if (!$cache) {
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
        return md5(self::getUserId() . '_' . \Yii::$app->session->getId());
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
                if (!\Yii::$app->user->isGuest) {
                    $userId = \Yii::$app->user->identity->id;
                }
            }
        } catch (\Exception $ex) {
            \Yii::getLogger()->log($ex->getMessage(), Logger::LEVEL_ERROR);
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
            if (!empty($cache)) {
                $cache->delete(self::key());
            }
        } catch (\Exception $ex) {
            \Yii::getLogger()->log($ex->getMessage(), Logger::LEVEL_ERROR);
        }
    }

    /**
     * create the crumb and send it to the trail
     *
     * @param $label
     * @param string|null $url
     * @param null $template
     * @throws \yii\base\InvalidConfigException
     */
    public static function add($label, $url = null, $template = null, $remove_action = null)
    {
        $request = \Yii::$app->request;

        $currentModule = \Yii::$app->controller->module->id;
        $currentController = \Yii::$app->controller->id;
        //  rimosso truncate per dettaglio  task - 7737
        //  $label = StringHelper::truncate($label, 20, '...');
        $isIndex = self::setCurrentIndex();


        if (!$request->isPjax && $url != '/site/error') {
            if (empty($url)) {
                $url = $request->url;
            }

            self::clearCache();

            //BREADCRUMB COMMUNITY -> HOME/COMMUNITY/NAME_COMMUNITY/...
            if (empty(\Yii::$app->controller->view->params['forceBreadcrumbsNoCommunity'])
                || (!empty(\Yii::$app->controller->view->params['forceBreadcrumbsNoCommunity']) && \Yii::$app->controller->view->params['forceBreadcrumbNoCommunity'] === false)) {
                $isSetBreadcrumbCommunityIndex = self::createCrumbCommunity($currentController);
            }

            //PERSONALIZED BREADCRUMB
            if (!empty(\Yii::$app->controller->view->params['forceBreadcrumbs'])) {
                self::personalizedBreadcrumbs();
            } else {
                //NORMAL BREADCRUMB  ->  HOME/INDEX/DETAIL
                if ($currentController != 'join') {
                    $crumbIndex = self::createCrumb(compact('label', 'url', 'template', 'remove_action'), self::TYPE_BREAD_INDEX);
                    $crumb = self::createCrumb(compact('label', 'url', 'template', 'remove_action'));

                    if (!$isSetBreadcrumbCommunityIndex || ($isSetBreadcrumbCommunityIndex && $currentModule != 'community')) {
                        //  CRUMB INDEX IS a link to a index and LABEL is the the generic NAME of the object or nodule name.
                        self::addToTrail($crumbIndex);
                    }

                    if (!$isIndex) {
                        // CRUMB DETAIL IS without LINK and LABEL is the title of the page
                        self::addToTrail($crumb);
                    }
                }
            }


            if (self::$reload) {
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
        self::crumbExistInTrail($crumb, $trail);

//        if (self::check($crumb, $trail)) {
        $trail[] = $crumb;
        \Yii::$app->trigger(self::EVENT_CRUMB_ADDED, new Event(['sender' => ['data' => $crumb]]));
        self::clearCache();
        self::getCache()->set(self::key(), $trail);
//        }
    }

    public static function crumbExistInTrail($crumb, $trail)
    {
//        $trail = self::getCache()->get(self::key());
        foreach ($trail as $elem) {
            if ($crumb->url == $elem->url) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     */
    public static function lastCrumbUrl()
    {
        $lastIndex = \Yii::$app->session->get('lastBreadcrumbIndex');
        if (!empty($lastIndex['url'])) {
            return $lastIndex['url'];
        } else {
            return '/' . \Yii::$app->controller->module->id . '/' . \Yii::$app->controller->id;
        }
    }


    /**
     * @param $trail
     */
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
        if ($trail) {
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
        foreach ($array as $key => $value) {
            $keys[] = $key;
            if (is_array($value)) {
                $keys = array_merge($keys, self::getKeysMultidimensional($value));
            }
        }
        return $keys;
    }

    /**
     * @param null $array_url
     * @return Crumb
     * @throws \yii\base\InvalidConfigException
     */
    public static function createCrumb($array_url = null, $type = self::TYPE_BREAD_DETAIL, $showIndex = false)
    {
        $title = '';
        $name_class = '';
        $isCurrentIndex = self::isIndex(\Yii::$app->controller->action->id, \Yii::$app->controller->id, \Yii::$app->controller->module);

        list($controller, $actionID) = \Yii::$app->createController($array_url['url']);
        $controller = \Yii::$app->controller;
        if ($controller instanceof BaseController) {
            $model = $controller->model;
            if(method_exists($model, 'getTitle')){
                $title = $model->getTitle();
            }else {
                $title = $model->__toString();
            }

            if($type != self::TYPE_BREAD_DETAIL){
                $title = StringHelper::truncate($title, 20, '...');
            }
            $name_class = $controller->model->className();
        }

        $crumb = new Crumb();
        $crumb->label = !empty($array_url['label']) ? $array_url['label'] : $title;
        $crumb->title = !empty($array_url['title']) ? $array_url['title'] : $crumb->label;

        if ($type == self::TYPE_BREAD_COMMUNITY) {
            $crumb->url = $array_url['url'];
        } else {
            $crumb->url = null;
        }
//        $crumb->url = $array_url['url'];
        $crumb->controller = $controller->id;
        $crumb->model = $name_class;
        $crumb->module = $controller->module->className();
        $action_name = explode('?', $actionID);
        $crumb->action = reset($action_name);
        $crumb->route = $controller->route;
        $crumb->visible = true;

        // INDEX BREADCRUMB
        if ($type == self::TYPE_BREAD_INDEX) {
            $crumb = self::setUrlCrumbIndex($crumb, $controller, $array_url, $showIndex, $isCurrentIndex);
        }

        if (isset($controller->actionParams) && sizeof($controller->actionParams)) {
            $crumb->params = $controller->actionParams;
        }

        return $crumb;
    }


    /**
     * @param $crumb
     * @param $controller
     * @param $array_url
     * @param $showIndex
     * @param $isCurrentIndex
     * @return mixed
     */
    public static function setUrlCrumbIndex($crumb, $controller, $array_url, $showIndex, $isCurrentIndex)
    {
        $lastBreadcrumbIndex = \Yii::$app->session->get('lastBreadcrumbIndex');
        $moduleName = $controller->module->id;
        $controllerName = $controller->id;
        if (!empty($array_url['module'])) {
            $moduleName = $array_url['module'];
        }
        if (!empty($array_url['controller'])) {
            $controllerName = $array_url['controller'];
        }

        // --------- LABEL  CRUMB
        $module = \Yii::$app->getModule($moduleName);
        //defaut values for breadcrumb index
        $crumb->label = $moduleName;
        if (!empty($module->name)) {
            $crumb->label = $module->name;
            $crumb->title = $module->name;

        }
        // personalized label in module plugin
        if ($module instanceof \open20\amos\core\interfaces\BreadcrumbInterface) {
            if (!empty($module->getControllerNames()[$controllerName])) {
                $crumb->label = $module->getControllerNames()[$controllerName];
                $crumb->title = $module->getControllerNames()[$controllerName];

            }
        }


        // --------- URL CRUMB
        // breadcumb index default go to /module/controller/index
        if ($isCurrentIndex && !$showIndex) {
            $crumb->url = null;
            $crumb->route = null;
        } else {
            // url index default - go /modulename/controllername/index of the current controller
            $crumb->url = '/' . $moduleName . '/' . $controllerName. '/index';
            $crumb->route = '/' . $moduleName . '/' . $controllerName. '/index';

            //personalized url index to model for a particular controller
            if (!\Yii::$app->user->isGuest) {
                // logged
                if (method_exists($module, 'defaultControllerIndexRoute')) {
                    $defaultRoute = $module->defaultControllerIndexRoute();
                    if (!empty($defaultRoute[$controller->id])) {
                        $crumb->url = $defaultRoute[$controller->id];
                        $crumb->route = $defaultRoute[$controller->id];
                    }
                }
            } else {
                //slogged
                if (method_exists($module, 'defaultControllerIndexRouteSlogged')) {
                    $defaultRoute = $module->defaultControllerIndexRouteSlogged();
                    if (!empty($defaultRoute[$controller->id])) {
                        $crumb->url = $defaultRoute[$controller->id];
                        $crumb->route = $defaultRoute[$controller->id];
                    }
                }
            }

            if (isset(\Yii::$app->params['positionalBreadcrumbLastVisitedIndex']) && \Yii::$app->params['positionalBreadcrumbLastVisitedIndex'] == true) {
                // url index - back to last visited index
                if (!empty($lastBreadcrumbIndex)) {
                    if ($lastBreadcrumbIndex['module'] == $moduleName && $lastBreadcrumbIndex['module'] == $controllerName) {
                        $crumb->url = $lastBreadcrumbIndex['url'];
                        $crumb->route = $lastBreadcrumbIndex['url'];
                    }
                }
            }
        }

        $crumb->remove_action = isset($array_url['remove_action']) ? $array_url['remove_action'] : null;
        $crumb->params = [];
        return $crumb;
    }

    /**
     * @param $actionId
     * @param $module
     * @return bool
     */
    public static function isIndex($actionId, $controllerId, $module)
    {
        $defaultIndexActions = ['index', 'all', 'own', 'created-by'];
        $backListIndexActions = ['community/join/index', 'community/configure-dashboard/index'];
        if ($module instanceof \open20\amos\core\interfaces\BreadcrumbInterface) {
            $action = $controllerId . '/' . $actionId;
            if (in_array($action, $module->getIndexActions())) {
                return true;
            }
        } else {
//            pr($module.'/'.$controllerId.'/'.$actionId, 'sdddd');
            foreach ($defaultIndexActions as $index) {
                $path = \Yii::$app->controller->module->id . '/' . \Yii::$app->controller->id . '/' . $index;
                if (in_array($path, $backListIndexActions)) {
                    return false;
                }
                if (strpos($actionId, $index) !== false) {
                    return true;
                }
            }

        }
        return false;
    }


    /**
     * @return bool
     */
    public static function setCurrentIndex()
    {
        $isIndex = self::isIndex(\Yii::$app->controller->action->id, \Yii::$app->controller->id, \Yii::$app->controller->module);
        if ($isIndex) {
            \Yii::$app->session->set('lastBreadcrumbIndex', [
                'module' => \Yii::$app->controller->module->id,
                'controller' => \Yii::$app->controller->id,
                'action' => \Yii::$app->controller->action->id,
                'url' => \Yii::$app->request->url,
            ]);
        }
        return $isIndex;

    }


    /**
     * @param $community
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public static function createCrumbParentCommunities($community)
    {
        $communityParent = $community;
        $parentCommunities = [];
        while (!empty($communityParent->parent_id)) {
            $communityParent = \open20\amos\community\models\Community::findOne($communityParent->parent_id);
            $parentCommunities[] = $communityParent;
        }
        $parentsCrumb = [];
        foreach ($parentCommunities as $parentCom) {

            // crumb - index subcommunity
            $url_param = [
                'label' => \Yii::t('amoscore', 'Subcommunity'),
                'module' => 'community',
                'controller' => 'community',
                'url' => '/community/subcommunities/my-communities?id=' . $parentCom->id,
                'route' => '/community/subcommunities/my-communities?id=' . $parentCom->id,
            ];
            $parentsCrumb [] = self::createCrumb($url_param, self::TYPE_BREAD_COMMUNITY);

            //crumb  - community name
            $url_param = [
                'label' => StringHelper::truncate($parentCom->getTitle(), 20, '...'),
                'title' => $parentCom->getTitle(),
                'module' => 'community',
                'controller' => 'community',
                'url' => '/community/join?id=' . $parentCom->id,
                'route' => '/community/join?id=' . $parentCom->id,
            ];
            $parentsCrumb [] = self::createCrumb($url_param, self::TYPE_BREAD_COMMUNITY);
        }
        return $parentsCrumb;
    }

    /**
     * @param $currentController
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public static function createCrumbCommunity($currentController)
    {
        $moduleCwh = \Yii::$app->getModule('cwh');
        $community_id = null;
        isset($moduleCwh) ? $scope = $moduleCwh->getCwhScope() : null;
        if ($scope && $scope['community']) {
            $community_id = $scope['community'];
        }

        $isSetBreadcrumbCommunityIndex = false;
        if (!empty($community_id)) {
            $community = \open20\amos\community\models\Community::findOne($community_id);
            if ($community) {
                $url_param = [
                    'label' => StringHelper::truncate($community->getTitle(), 20, '...'),
                    'title' => $community->getTitle(),
                    'module' => 'community',
                    'controller' => 'community',
                    'url' => '/community/join?id=' . $community_id,
                    'route' => '/community/join?id=' . $community_id,
                    'remove_action' => isset($remove_action) ? $remove_action : null,
                    'template' => isset($template) ? $template : null,
                ];

                $crumbIndex = self::createCrumb($url_param, self::TYPE_BREAD_INDEX, true);
                $isSetBreadcrumbCommunityIndex = true;

                $parentsCrumb = self::createCrumbParentCommunities($community);

                if ($currentController != 'join') {
                    $crumb = self::createCrumb($url_param, self::TYPE_BREAD_COMMUNITY);
                } else {
                    $crumb = self::createCrumb($url_param, self::TYPE_BREAD_DETAIL);
                }

                self::addToTrail($crumbIndex);
                $parentsCrumb = array_reverse($parentsCrumb);
                foreach ($parentsCrumb as $crumbCom) {
                    self::addToTrail($crumbCom);
                }
                self::addToTrail($crumb);
            }
        }

        return $isSetBreadcrumbCommunityIndex;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public static function personalizedBreadcrumbs()
    {
        $forcedBreadCrumbs = \Yii::$app->controller->view->params['forceBreadcrumbs'];
        $tot = count($forcedBreadCrumbs);
        $i = 1;
        foreach ($forcedBreadCrumbs as $fCrumb) {
            $url_param = [
                'label' => $fCrumb['label'],
                'url' => $fCrumb['url'],
                'route' => $fCrumb['url'],
                'remove_action' => isset($remove_action) ? $remove_action : null,
                'template' => isset($template) ? $template : null,
            ];
//            pr($url_param);
            if ($i == $tot) {
                $crumbDetail = self::createCrumb($url_param);
                self::addToTrail($crumbDetail);

            } else {
                $crumbIndex = self::createCrumb($url_param, self::TYPE_BREAD_COMMUNITY);
                self::addToTrail($crumbIndex);
            }
        }
    }


}
