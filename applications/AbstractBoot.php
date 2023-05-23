<?php
namespace open20\amos\core\applications;

use yii\base\Security;
use yii\helpers\StringHelper;


abstract class AbstractBoot
{
    const ProtectedPrefix = "ECRY_";
    
    protected $password = "";
    
    protected $config = [];
    
    protected $app;
    
    /**
     */
    public function __construct($config = null)
    {
        $this->password = ! is_null(getenv('ENC_KEY')) ? getenv('ENC_KEY') : "";
        if(!is_null($config)){
            $this->config = $this->parseForProtectedValues($config);
            $this->app = $this->createApplication($this->config);
        }
    }
    
    protected abstract function createApplication($config);
    
    /**
     *
     * @return \yii\web\Application
     */
    public function getApp()
    {
        return $this->app;
    }
    
    /**
     *
     * @return number
     */
    public function run()
    {
        return $this->app->run();
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

