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

use open20\amos\admin\AmosAdmin;
use Yii;
use yii\base\BaseObject;

class CurrentUser extends BaseObject
{
    static public $user;
    static public $userIdentity;
    static public $userProfile;

    static public function getUser()
    {
        if (!isset(self::$user)) {
            self::$user = Yii::$app->getUser();
        }
        return self::$user;
    }

    static public function getUserIdentity()
    {
        if (!isset(self::$userIdentity)) {
            if (!self::getUser()->getIsGuest()) {
                self::$userIdentity = self::getUser()->getIdentity();
            }
        }

        return self::$userIdentity;
    }

    static public function getUserProfile()
    {
        if (!isset(self::$userProfile)) {
            if (!Yii::$app->getUser()->getIsGuest() && AmosAdmin::instance()) {
                self::$userProfile = self::getUserIdentity()->userProfile;
            }
        }
        return self::$userProfile;
    }

    /**
     *
     * @return boolean
     */
    public static function isPlatformGuest()
    {
        $ret = true;

        if (!Yii::$app->getUser()->getIsGuest() &&
            (isset(Yii::$app->params['platformConfigurations']['guestUserId']) ? Yii::$app->getUser()->id
                != Yii::$app->params['platformConfigurations']['guestUserId'] : false)) {
            $ret = false;
        }

        return $ret;
    }
}