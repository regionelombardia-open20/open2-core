<?php
namespace open20\amos\core\applications;

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    [NAMESPACE_HERE]
 * @category   CategoryName
 */

use Exception;
use luya\Boot;
use ReflectionClass;
use Yii;
use yii\helpers\ArrayHelper;

class CmsBoot extends Boot
{
   /**
     * Run Web-Application based on the provided config file.
     *
     * @return string Returns the Yii Application run() method if mock is disabled. Otherwise returns void
     */
    public function applicationWeb()
    {
        $config = $this->getConfigArray();
        $this->includeYii();
        $mergedConfig = ArrayHelper::merge($config, ['bootstrap' => ['luya\web\Bootstrap']]);
        $this->app = new CmsApplication($mergedConfig);

        if (!$this->mockOnly) {
            return $this->app->run();
        }
    }

    /**
     * Helper method to check whether the provided Yii Base file exists, if yes include and
     * return the file.
     *
     * @return bool Return value based on require_once command.
     * @throws Exception Throws Exception if the YiiBase file does not exists.
     */
    protected function includeYii()
    {
        if (file_exists($this->getBaseYiiFile())) {
            defined('LUYA_YII_VENDOR') ?: define('LUYA_YII_VENDOR', dirname($this->getBaseYiiFile()));

            $baseYiiFolder = LUYA_YII_VENDOR . DIRECTORY_SEPARATOR;
            $luyaYiiFile = $this->getCoreBasePath() . DIRECTORY_SEPARATOR .  'Yii.php';

            if (file_exists($luyaYiiFile)) {
                require_once($baseYiiFolder . 'BaseYii.php');
                require_once($luyaYiiFile);
            } else {
                require_once($baseYiiFolder . 'Yii.php');
            }

            Yii::setAlias('@luya', $this->getCoreBasePath());

            return true;
        }

        throw new Exception("YiiBase file does not exits '".$this->getBaseYiiFile()."'.");
    }

    /**
     * Returns the path to luya core files
     *
     * @return string The base path to the luya core folder.
     */
    public function getCoreBasePath()
    {
        $reflector = new ReflectionClass(Boot::class);
        return dirname($reflector->getFileName());
    }
    
    /**
     * 
     * @return type
     */
    public static function isSessionDebug(){
        
        $debug = (!is_null($SESSION['debug_session']) ? $SESSION['debug_session'] == 'on' : false);
     
        return $debug;
    }
}