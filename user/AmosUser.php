<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\user
 * @category   CategoryName
 */

namespace open20\amos\core\user;

use open20\amos\admin\models\UserProfile;
use open20\amos\core\controllers\BaseController;
use Yii;
use yii\base\Event;
use yii\log\Logger;
use yii\web\User;

/**
 * Class AmosUser
 * @package open20\amos\core\user
 */
class AmosUser extends User
{
    public $identityClass = '\open20\amos\core\user\User';
    public $authKeyParam = '__amosAuthKey';

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

            if ($profile) {
                if (!(!empty(\Yii::$app->params['performance']) && \Yii::$app->params['performance']
                    == true && \Yii::$app->formatter->asDate($profile->ultimo_accesso,
                        'php:Y-m-d') == date('Y-m-d'))) {
                    $time                    = new \DateTime("now");
                    $profile->ultimo_accesso = Yii::$app->formatter->asDate(
                        $time, 'php:Y-m-d H:i:s'
                    ); // 2014-10-06 15:22:34;
                    $profile->count_logins   = $profile->count_logins + 1;
                    $profile->detachBehavior("TimestampBehavior");
                    $profile->save(false);
                }
            }
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

            if (!(!empty(\Yii::$app->params['performance']) && \Yii::$app->params['performance']
                == true)) {
                $time                   = new \DateTime("now");
                $profile->ultimo_logout = Yii::$app->formatter->asDate($time,
                    'php:Y-m-d H:i:s'); // 2014-10-06 15:22:34;
                $profile->save(false);
            }
        } catch (\Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), Logger::LEVEL_ERROR);
        }
    }
    /*
     * @inheritdoc
     */

    public function can($permissionName, $params = [], $allowCaching = true)
    {
        if (empty($params)) {
            $controller = Yii::$app->controller;
            if (!is_null($controller) && $controller instanceof BaseController) {
                $params['model'] = $controller->getModelObj();
            }
        }
        return $can = parent::can($permissionName, $params, $allowCaching);
    }
    
    /**
     * 
     * @param User $identity
     * @return bool
     */
    protected function beforeLogout($identity) {
        $result = parent::beforeLogout($identity);

        if (!empty(\Yii::$app->params['enableRenewAuthKey']) && \Yii::$app->params['enableRenewAuthKey'] == true) {
            $identity->generateAuthKey();
            $identity->save(false);
        }
        return $result;
    }

    /**
     * 
     * @param User $identity
     * @param bool $cookieBased
     * @param int $duration
     * @return bool
     */
    protected function beforeLogin($identity, $cookieBased, $duration) {
        $originalResult = parent::beforeLogin($identity, $cookieBased, $duration);

        if (empty($identity->auth_key) || strlen($identity->auth_key) < 32) {
            $identity->generateAuthKey();
            $identity->save(false);
        }
        return $originalResult;
    }
}