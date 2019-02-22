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

use Yii;

/**
 * Class BackendController
 * @package lispa\amos\core\controllers
 */
abstract class BackendController extends AmosController
{
    public $layout = 'main';

    /**
     * @param null $layout
     * @return bool
     */
    public function setUpLayout($layout = null)
    {
        if ($layout === false) {
            $this->layout = false;
            return true;
        }
        $this->layout = (!empty($layout)) ? $layout : $this->layout;
        $module = \Yii::$app->getModule('layout');
        if (empty($module)) {
            if (strpos($this->layout, '@') === false) {
                $this->layout = '@vendor/lispa/amos-core/views/layouts/' . (!empty($layout) ? $layout : $this->layout);
            }
            return true;
        }
        return true;
    }

    /**
     * If not present, add flash message to session
     *
     * @param string $key - 'danger', 'warning', 'success'
     * @param string $message
     */
    public function addFlash($key, $message)
    {
        $flashes = Yii::$app->session->getFlash($key);
        if (!Yii::$app->session->hasFlash($key) || !in_array($message, $flashes)) {
            Yii::$app->getSession()->addFlash($key, $message);
        }
    }
}
