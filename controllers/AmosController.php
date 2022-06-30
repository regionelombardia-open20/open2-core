<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\controllers
 * @category   CategoryName
 */

namespace open20\amos\core\controllers;

use open20\amos\core\components\AmosView;
use open20\amos\core\module\BaseAmosModule;
use open20\amos\core\utilities\ClassUtility;
use open20\amos\core\utilities\Email;
use open20\amos\dashboard\models\AmosUserDashboards;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\ViewEvent;
use yii\helpers\ArrayHelper;
use yii\web\Controller as YiiController;
use yii\web\View;
use const YII_DEBUG;
use const YII_ENV_PROD;
use const YII_ENV_TEST;

/**
 * Class AmosController
 * @package open20\amos\core\controllers
 */
abstract class AmosController extends YiiController
{
    /**
     * Custom Init
     */
    public function init()
    {
        parent::init();

        // enable content compression to remove whitespace
        if (!YII_DEBUG && (YII_ENV_PROD || YII_ENV_TEST) && ClassUtility::objectHasProperty($this->module, 'contentCompression') && $this->module->contentCompression) {
            $this->view->on(View::EVENT_AFTER_RENDER, [$this, 'minify']);
        }
        
        if ($this->module instanceof BaseAmosModule) {
            /**
             * @var array $currentControllerMetadata
             */
            $controllerMetadata = isset($this->module->pluginMetadata[$this->id]) ? $this->module->pluginMetadata[$this->id] : [];

            //If is set metadata for this module/controller
            if (!empty($controllerMetadata)) {
                //Setup icons and color for plugin
                $this->getView()->setPluginIcon($controllerMetadata['pluginIcon']);
                $this->getView()->setPluginName($controllerMetadata['pluginName']);
                $this->getView()->setPluginColor($controllerMetadata['pluginColor']);
            }
        }
    }
    
    /**
     * Minify the view content.
     *
     * @param ViewEvent $event
     * @return string
     */
    public function minify($event)
    {
        return $event->output = $this->view->compress($event->output);
    }

    /**
     * Renders a view without applying layout.
     * This method differs from [[render()]] in that it does not apply any layout.
     * @param string $view the view name. Please refer to [[render()]] on how to specify a view name.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * @param integer $user_id for get user configurations.
     * @return string the rendering result.
     */
    public function renderMailPartial($view, $params = array(), $user_id = null)
    {
        return Email::renderMailPartial($view, $params, $user_id);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $vanishTableName = 'vanish_cache';
        $enablePageCache = !empty(Yii::$app->params['enablePageCache']) ? Yii::$app->params['enablePageCache'] : false;
        if ($enablePageCache && Yii::$app->db->schema->getTableSchema($vanishTableName, true) != null) {
            $enablePageCache = Yii::$app->response->statusCode < 300;
            $findDash = AmosUserDashboards::find();
            $findDash->andWhere(['user_id' => Yii::$app->user->id]);
            $findDash->orderBy(['updated_at' => SORT_DESC]);
            $userDashboard = $findDash->one();

            //Main Cache filter
            $amosCache = [
                'class' => 'yii\filters\PageCache',
                'enabled' => $enablePageCache,
                //'only' => ['index'],
                'duration' => 8600,
                'cacheHeaders' => false,
                'cacheCookies' => false,
                'varyByRoute' => true,
                'variations' => [
                    Yii::$app->language,
                    Yii::$app->user->id,
                    Yii::$app->request->get(),
                    Yii::$app->request->post(),
                    Yii::$app->session->get('cwh-scope'),
                    Yii::$app->session->get('cwh-relation-table'),
                    $userDashboard ? $userDashboard->updated_at : ''
                ],
                'dependency' => [
                    'class' => 'yii\caching\DbDependency',
                    'sql' => 'SELECT MAX(updated_at) FROM ' . $vanishTableName,
                ]
            ];

            $behaviors = ArrayHelper::merge(parent::behaviors(),
                [
                    'amoscache' => $amosCache,
                ]);

            return $behaviors;
        } else {
            return parent::behaviors();
        }
    }

    /**
     * override Renders a view without applying layout.
     * This method differs from [[render()]] in that it does not apply any layout.
     * @param string $view the view name. Please refer to [[render()]] on how to specify a view name.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * @return string the rendering result.
     * @throws InvalidArgumentException if the view file does not exist.
     */
    public function renderPartial($view, $params = [])
    {
        /** @var AmosView $viewObj */
        $viewObj = $this->getView();
        $view = $viewObj->changeView($view);
        return parent::renderPartial($view, $params);
    }

    /**
     * override Renders a view
     * @param string $view the view name.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * These parameters will not be available in the layout.
     * @return string the rendering result.
     * @throws InvalidArgumentException if the view file or the layout file does not exist.
     */
    public function render($view, $params = [])
    {
        /** @var AmosView $viewObj */
        $viewObj = $this->getView();
        $view = $viewObj->changeView($view);
        return parent::render($view, $params);
    }

    /**
     * @param integer $user_id
     */
    protected function setUserLanguage($user_id)
    {
        Email::setUserLanguage($user_id);
    }
}
