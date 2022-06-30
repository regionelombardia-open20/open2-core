<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\utilities
 * @category   CategoryName
 */

namespace open20\amos\core\utilities;

use open20\amos\core\migration\libs\common\MigrationCommon;
use open20\amos\core\module\BaseAmosModule;
use yii\log\Logger;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

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
            (in_array($_SERVER['REMOTE_ADDR'], \Yii::$app->params['seeLoginFormAllowedIps']) || in_array($_SERVER['HTTP_X_FORWARDED_FOR'], \Yii::$app->params['seeLoginFormAllowedIps']))
        );
    }
    
    /**
     * This method add an error message for each type of application, console or web.
     * @param string $errorMsg
     */
    public static function printErrorMessage($errorMsg)
    {
        if (\Yii::$app instanceof \yii\web\Application) {
            \Yii::$app->getSession()->addFlash('danger', $errorMsg);
        } elseif (\Yii::$app instanceof \yii\console\Application) {
            MigrationCommon::printConsoleMessage($errorMsg);
        } else {
            \Yii::getLogger()->log($errorMsg, Logger::LEVEL_ERROR);
        }
    }
    
    /**
     * This method throw the standard ForbiddenHttpException
     * @throws ForbiddenHttpException
     */
    public static function throwStandardForbiddenException()
    {
        throw new ForbiddenHttpException(BaseAmosModule::t('amoscore', 'Non sei autorizzato a visualizzare questa pagina'));
    }
    
    /**
     * This method throw the standard NotFoundHttpException
     * @throws NotFoundHttpException
     */
    public static function throwStandardNotFoundException()
    {
        throw new NotFoundHttpException(BaseAmosModule::t('amoscore', 'The requested page does not exist.'));
    }
}
