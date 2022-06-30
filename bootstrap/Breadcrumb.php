<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\bootstrap
 * @category   CategoryName
 */

namespace open20\amos\core\bootstrap;

use open20\amos\core\controllers\CrudController;
use open20\amos\core\helpers\PositionalBreadcrumbHelper;
use yii\base\BootstrapInterface;
use yii\base\Event;
use yii\base\WidgetEvent;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/**
 * Class Breadcrumb
 * @package open20\amos\core\bootstrap
 */
class Breadcrumb implements BootstrapInterface
{

    const EVENT_BEFORE_BREADCRUMB = 'event_before_breadcrumb';

    /**
     * @var string
     */
    public $activeBreadcrumbHelperClass = "open20\\amos\\core\\helpers\\BreadcrumbHelper";
//    public $activeBreadcrumbHelperClass = "open20\\amos\\core\\helpers\\PositionalBreadcrumbHelper";

    /**
     * @param $app
     */
    public function bootstrap($app)
    {
        Event::on(Breadcrumbs::className(), Breadcrumbs::EVENT_BEFORE_RUN, [$this, 'modifyBreadcrumbs']);
        Event::on(CrudController::className(), self::EVENT_BEFORE_BREADCRUMB, [$this, 'scopeCommunity']);
    }

    /**
     * Set scope community if content is published for community
     */
    public function scopeCommunity()
    {
        if ((method_exists(\Yii::$app->controller, 'getModel') || property_exists(\Yii::$app->controller, 'model'))
            &&  !empty(\Yii::$app->controller->model)) {
            $model = \Yii::$app->controller->model;
            if(\Yii::$app->controller->action->id == 'index' && \Yii::$app->controller->id == 'community'){
                $moduleCwh = \Yii::$app->getModule('cwh');
                if (isset($moduleCwh)) {
                    $moduleCwh->resetCwhScopeInSession();
                }
            }

            if (get_class($model) != 'open20\amos\community\models\Community' && !empty($model) && property_exists($model, 'isNewRecord') && !$model->isNewRecord) {
                $cwhModule = \Yii::$app->getModule('cwh');

                $communityModule = \Yii::$app->getModule('community');

                if (!empty($cwhModule) && !empty($communityModule) && !empty($model->destinatari) && !$cwhModule->enableDestinatariFatherChildren) {
                    $value = reset($model->destinatari);
                    if (!empty($value)) {
                        $community_id = str_replace('community-', '', $value);
                        $cwhModule->setCwhScopeInSession([
                            'community' => $community_id,
                        ]);
                    }
//                pr($model->destinatari,'destinatari');die;
                }
            }
        }
    }


    /**
     * @param WidgetEvent $event
     * @throws \yii\base\InvalidConfigException
     */
    public function modifyBreadcrumbs(WidgetEvent $event)
    {
        \Yii::$app->controller->trigger(self::EVENT_BEFORE_BREADCRUMB);

        if (!empty(\Yii::$app->params['enablePositionalBreadcrumb']) && \Yii::$app->params['enablePositionalBreadcrumb'] == true) {
            $this->activeBreadcrumbHelperClass = "open20\\amos\\core\\helpers\\PositionalBreadcrumbHelper";
        }
        /**
         * set up breadcrumb helper
         */
        $helperClass = \Yii::createObject($this->activeBreadcrumbHelperClass);

        /**
         * retrieve label
         */
        $lbl = end($event->sender->links);
        $result = reset($event->sender->links);
        if (is_object($lbl) && method_exists($lbl, '__toString')) {
            $lbl = $lbl->__toString();
        }
        $label = isset($lbl['label']) ? $lbl['label'] : $lbl;
        $hidden = isset($lbl['hidden']) ? $lbl['hidden'] : false;
        $template = isset($result['template']) ? $result['template'] : null;
        $remove_action = isset($result['remove_action']) ? $result['remove_action'] : null;


        /**
         * add crumb and generate cache again
         */
        if (!$hidden) {
            $helperClass::add($label, Url::current(), $template, $remove_action);
        }
        $trail = $helperClass->renderCrumbs();

        /**
         * if label is empty log in into breadcrumbs log
         */
        if (empty($label)) {
            /**
             * set logger stack info to zero
             */
            \Yii::getLogger()->traceLevel = 0;
            /**
             * log missing label
             */
            \Yii::info($trail, 'Breadcrumb');
        }

        /**
         * send cache to breadcrumb widget
         */
        //pr($trail,"Trail");
        $event->sender->links = $trail;
    }
}
