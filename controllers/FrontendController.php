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

/**
 * Class FrontendController
 * @package lispa\amos\core\controllers
 */
abstract class FrontendController extends AmosController
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $siteManagementModule = \Yii::$app->getModule('sitemanagement');
        if (!is_null($siteManagementModule)) {
            /** @var \amos\sitemanagement\Module $siteManagementModule */
            $siteManagementModule->registerMetadata();
        }

        return true;
    }
}
