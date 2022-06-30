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

use open20\amos\admin\AmosAdmin;
use open20\amos\core\interfaces\ApplicationInterface;

/**
 * Class WebApplication
 * @package open20\amos\core\applications
 */
class WebApplication extends \yii\web\Application implements ApplicationInterface
{
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
        return true;
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
        $ret = true;
        $module = AmosAdmin::instance();
        if(!is_null($module))
        {
            $ret = !$module->hideStandardLoginPageSection;
        }
        
        return $ret;
    }
}
