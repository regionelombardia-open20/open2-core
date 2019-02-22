<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\user
 * @category   CategoryName
 */

namespace lispa\amos\core\user;

use lispa\amos\admin\models\UserProfile;
use lispa\amos\core\controllers\BaseController;
use Yii;
use yii\base\Event;
use yii\log\Logger;
use yii\web\User;

/**
 * Class AmosUser
 * @package lispa\amos\core\user
 */
class AmosUser extends User
{
    public $identityClass = '\lispa\amos\core\user\User';
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->on(self::EVENT_AFTER_LOGIN, [$this, 'timeStampLogin']);
        $this->on(self::EVENT_BEFORE_LOGOUT, [$this, 'timeStampLogout']);
        parent::init();
    }
    
    /**
     * @param Event $event
     */
    public function timeStampLogin($event)
    {
        try {
            /** @var UserProfile $profile */
            $profile = $this->getIdentity()->getProfile();
            $time = new \DateTime("now");
            $profile->ultimo_accesso = Yii::$app->formatter->asDate($time, 'php:Y-m-d H:i:s'); // 2014-10-06 15:22:34;
            $profile->detachBehavior("TimestampBehavior");
            $profile->save(false);
        } catch (\Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), Logger::LEVEL_ERROR);
        }
    }
    
    /**
     * @param Event $event
     */
    public function timeStampLogout($event)
    {
        try {
            /** @var UserProfile $profile */
            $profile = $this->getIdentity()->getProfile();
            $time = new \DateTime("now");
            $profile->ultimo_logout = Yii::$app->formatter->asDate($time, 'php:Y-m-d H:i:s'); // 2014-10-06 15:22:34;
            $profile->save(false);
        } catch (\Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), Logger::LEVEL_ERROR);
        }
    }

    /*
     * @inheritdoc
     */
    public function can($permissionName, $params = [], $allowCaching = true)
    {
        if(empty($params)) {
            $controller = Yii::$app->controller;
            if (!is_null($controller) && $controller instanceof BaseController) {
                $params['model'] = $controller->getModelObj();
            }
        }
        return $can = parent::can($permissionName, $params, $allowCaching);

    }


}
