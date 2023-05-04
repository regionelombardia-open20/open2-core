<?php
namespace open20\amos\core\applications;

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package [NAMESPACE_HERE]
 * @category CategoryName
 */
use open20\amos\core\helpers\StringHelper;
use Exception;
use ReflectionClass;
use Yii;
use luya\Boot;
use yii\helpers\ArrayHelper;
use yii\base\Security;

/**
 * Add security on password in config files based on prefix in Key Name.
 *
 * export ENC_KEY=1234567890 // This is just a sample key, obviously
 *
 * <VirtualHost *:80>
 * ServerAdmin webmaster@localhost
 * DocumentRoot /var/www/html
 *
 * ErrorLog ${APACHE_LOG_DIR}/error.log
 * CustomLog ${APACHE_LOG_DIR}/access.log combine
 *
 * SetEnv ENC_KEY ${ENC_KEY}
 * </VirtualHost>
 *
 * nel file di configurazione per esempio main.local:
 * 'components' => [
 * 'db' => [
 * 'class' => 'yii\db\Connection',
 * 'dsn' => 'mysql:host=localhost;dbname=db_name',
 * 'ECRY_username' => 'zxbcvYundms909',
 * 'ECRY_password' => 'azert78999989jy',
 * 'charset' => 'utf8',
 * 'enableSchemaCache' => true,
 * 'schemaCacheDuration' => 88000,
 * 'schemaCache' => 'schemaCache',
 * 'attributes' => [PDO::ATTR_CASE => PDO::CASE_LOWER],//Enable on MySQL 8.X
 * ],
 * 'mailer' => [
 * 'class' => 'yii\swiftmailer\Mailer',
 * 'viewPath' => '@common/mail',
 * // send all mails to a file by default. You have to set
 * // 'useFileTransport' to false and configure a transport
 * // for the mailer to send real emails.
 * 'useFileTransport' => true,
 * ],
 * ],
 */
class CmsBoot extends Boot
{

    const ProtectedPrefix = "ECRY_";

    private $password = "";
    
    protected $_configArray;

    /**
     */
    public function __construct()
    {
        $this->password = ! is_null(getenv('ENC_KEY')) ? getenv('ENC_KEY') : "";
    }

    /**
     * Run Web-Application based on the provided config file.
     *
     * @return string Returns the Yii Application run() method if mock is disabled. Otherwise returns void
     */
    public function applicationWeb()
    {
        $config = $this->getConfigArray();
        $this->includeYii();
        $mergedConfig = ArrayHelper::merge($config, [
            'bootstrap' => [
                'luya\web\Bootstrap'
            ]
        ]);
        $this->app = new CmsApplication($mergedConfig);

        if (! $this->mockOnly) {
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
            $luyaYiiFile = $this->getCoreBasePath() . DIRECTORY_SEPARATOR . 'Yii.php';

            if (file_exists($luyaYiiFile)) {
                require_once ($baseYiiFolder . 'BaseYii.php');
                require_once ($luyaYiiFile);
            } else {
                require_once ($baseYiiFolder . 'Yii.php');
            }

            Yii::setAlias('@luya', $this->getCoreBasePath());

            return true;
        }

        throw new Exception("YiiBase file does not exits '" . $this->getBaseYiiFile() . "'.");
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
     * @return bool
     */
    public static function isSessionDebug()
    {
        $debug = (! is_null($SESSION['debug_session']) ? $SESSION['debug_session'] == 'on' : false);

        return $debug;
    }

    /**
     *
     * {@inheritdoc}
     */
    public function getConfigArray()
    {
        if ($this->_configArray === null) {
            if (! file_exists($this->configFile)) {
                if (! $this->getIsCli()) {
                    throw new Exception("Unable to load the config file '" . $this->configFile . "'.");
                }

                $config = [
                    'id' => 'consoleapp',
                    'basePath' => dirname(__DIR__)
                ];
            } else {
                $config = require $this->configFile;
            }

            if (! is_array($config)) {
                throw new Exception("config file '" . $this->configFile . "' found but no array returning.");
            }

            // preset the values from the defaultConfigArray
            if (! empty($this->prependConfigArray())) {
                $config = ArrayHelper::merge($config, $this->prependConfigArray());
            }
            $config = $this->parseForProtectedValues($config);
            $this->_configArray = $config;
        }

        return $this->_configArray;
    }

    /**
     *
     * @param array $config
     */
    protected function parseForProtectedValues($config = [])
    {
        if (! empty($this->password)) {
            $config = $this->loopOnArray($config);
        }
        return $config;
    }

    /**
     *
     * @param array $ary
     */
    protected function loopOnArray($ary = [])
    {
        $security = new Security();

        foreach ($ary as $key => $element) {
            if (is_array($element)) {
                $ary[$key] = $this->loopOnArray($element);
            } else {
                if (str_starts_with($key, static::ProtectedPrefix)) {
                    $ary[str_replace(static::ProtectedPrefix, "", $key)] = $security->decryptByKey(StringHelper::base64UrlDecode($element), $this->password);
                    unset($ary[$key]);
                }
            }
        }
        return $ary;
    }

    /**
     *
     * @param string $data
     * @return string
     */
    public function encryptValue($data)
    {
        $security = new Security();
        $pwd = $security->encryptByKey($data, $this->password);
        return StringHelper::base64UrlEncode($pwd);
    }
}