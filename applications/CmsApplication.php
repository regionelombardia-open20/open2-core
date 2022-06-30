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
use luya\web\Application as WebApplication;
use Yii;

/**
 * Class CmsApplication
 * @package open20\amos\core\applications
 */
class CmsApplication extends WebApplication implements ApplicationInterface
{
    /**
     * @return string
     */
    public function getHomeUrl()
    {
        return self::toUrl(parent::getHomeUrl());
    }
    
    /**
     * @param string $url
     * @return string
     */
    public static function toUrl($url)
    {
        $languageString = '/' . Yii::$app->composition['langShortCode'];
        if (strncmp($url, $languageString, strlen($languageString)) === 0) {
            $languageString = "";
        }
        $url = (strcmp($url, '/') === 0) ? "" : $url;
        return $languageString . '/' . $url;
    }
    
    /**
     * @inheritdoc
     */
    public function isConsoleApplication()
    {
        return false;
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
        return true;
    }
    
    /**
     * 
     * @return boolean
     */
    public function isBasicAuthEnabled()
    {
        $ret = true;
        $module = Yii::$app->getModule('userauthfrontend');
        if(!is_null($module))
        {
            $ret = $module->enableUserPasswordLogin;
        }
        
        return $ret;
    }
}
