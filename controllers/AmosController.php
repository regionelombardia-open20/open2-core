<?php
/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\controllers
 * @category   CategoryName
 */

namespace lispa\amos\core\controllers;

use lispa\amos\core\module\BaseAmosModule;
use lispa\amos\core\utilities\Email;
use yii\web\Controller as YiiController;

/**
 * Class AmosController
 * @package lispa\amos\core\controllers
 */
abstract class AmosController extends YiiController
{

    /**
     * Custom Init
     */
    public function init()
    {
        parent::init();

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
        $enablePageCache = !empty(\Yii::$app->params['enablePageCache']) ? \Yii::$app->params['enablePageCache'] : false;
        if ($enablePageCache && \Yii::$app->db->schema->getTableSchema($vanishTableName, true) != null) {
            $enablePageCache = \Yii::$app->response->statusCode < 300;
            $findDash = \lispa\amos\dashboard\models\AmosUserDashboards::find();
            $findDash->andWhere(['user_id' => \Yii::$app->user->id]);
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
                    \Yii::$app->language,
                    \Yii::$app->user->id,
                    \Yii::$app->request->get(),
                    \Yii::$app->request->post(),
                    \Yii::$app->session->get('cwh-scope'),
                    \Yii::$app->session->get('cwh-relation-table'),
                    $userDashboard ? $userDashboard->updated_at : ''
                ],
                'dependency' => [
                    'class' => 'yii\caching\DbDependency',
                    'sql' => 'SELECT MAX(updated_at) FROM ' . $vanishTableName,
                ]
            ];

            $behaviors = \yii\helpers\ArrayHelper::merge(parent::behaviors(),
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
     * @throws InvalidParamException if the view file does not exist.
     */
    public function renderPartial($view, $params = [])
    {
        $view = $this->getView()->changeView($view);
        return parent::renderPartial($view, $params, $this);
    }

    /**
     * override Renders a view
     * @param string $view the view name.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * These parameters will not be available in the layout.
     * @return string the rendering result.
     * @throws InvalidParamException if the view file or the layout file does not exist.
     */
    public function render($view, $params = [])
    {
        $view = $this->getView()->changeView($view);
        return parent::render($view, $params);
    }

    /**
     *
     * @param integer $user_id
     */
    protected function setUserLanguage($user_id)
    {
        Email::setUserLanguage($user_id);
    }
}