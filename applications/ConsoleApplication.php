<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    Open20Package
 * @category   CategoryName
 */

namespace open20\amos\core\applications;

use Yii;
use yii\console\Application;
use yii\web\User;

class ConsoleApplication extends Application
{

    public function init()
    {
        Yii::setAlias('@webroot', '@backend/web');
        Yii::setAlias('@web', '@backend/web');
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
}