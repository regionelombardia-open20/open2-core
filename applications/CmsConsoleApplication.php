<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\applications
 * @category   CategoryName
 */

namespace open20\amos\core\applications;

use open20\amos\core\interfaces\ApplicationInterface;
use Yii;
use yii\console\Application;
use yii\web\User;

/**
 * Class CmsConsoleApplication
 * @package open20\amos\core\applications
 */
class CmsConsoleApplication extends Application implements ApplicationInterface
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        Yii::setAlias('@webroot', '@frontend/web');
        Yii::setAlias('@web', '@frontend/web');
        parent::init();
    }

    /**
     * Returns the user component.
     * @return User the user component.
     */
    public function getUser()
    {
        return $this->get('user');
    }

    /**
     * Returns the session component.
     * @return Session the session component.
     */
    public function getSession()
    {
        return $this->get('session');
    }

    /**
     * @inheritdoc
     */
    public function isConsoleApplication()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isBackendApplication()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isCmsApplication()
    {
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function isBasicAuthEnabled()
    {
        return true;
    }
}
