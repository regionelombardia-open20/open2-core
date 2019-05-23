<?php

namespace lispa\amos\core\utilities;

use lispa\amos\core\utilities\Email;
use lispa\amos\core\user\User;
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
