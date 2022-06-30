<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\utilities
 * @category   CategoryName
 */

namespace open20\amos\core\utilities;

/**
 * Class CoreCommonUtility
 * @package open20\amos\core\utilities
 */
class CoreCommonUtility
{
    /**
     * This method is useful to check if the user is viewing a platform from headquarter or from another place.
     * Useful to enable offline mode only for other places, see login standard form from only main headquarter
     * or other functionalities. It return true if the user is in main headquarter. False otherwise.
     * @return bool
     */
    public static function platformSeenFromHeadquarter()
    {
        return (
            isset(\Yii::$app->params['seeLoginFormAllowedIps']) &&
            is_array(\Yii::$app->params['seeLoginFormAllowedIps']) &&
            !empty(\Yii::$app->params['seeLoginFormAllowedIps']) &&
            in_array($_SERVER['REMOTE_ADDR'], \Yii::$app->params['seeLoginFormAllowedIps'])
        );
    }
}
