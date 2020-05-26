<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    Open20Package
 * @category   CategoryName
 */

namespace open20\amos\core\utilities;

use open20\amos\core\utilities\Email;
use open20\amos\core\user\User;
use triscovery\esperienze\Module;
use Yii;

class ViewUtility {

    /**
     * 
     */
    public static function formatDateTime() {
        return (isset(Yii::$app->modules['datecontrol']['displaySettings']['datetime'])) 
            ? Yii::$app->modules['datecontrol']['displaySettings']['datetime'] 
            : 'd-m-Y H:i:s A';
    }

    /**
     * 
     * @return type
     */
    public static function formatDate() {
        return (isset(Yii::$app->modules['datecontrol']['displaySettings']['date'])) 
            ? Yii::$app->modules['datecontrol']['displaySettings']['date'] 
            : 'd-m-Y';
    }

}
