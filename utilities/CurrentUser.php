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
            if (!Yii::$app->getUser()->getIsGuest() && Yii::$app->getModule('admin')) {
                self::$userProfile = self::getUserIdentity()->userProfile;
            }
        }
        return self::$userProfile;
    }

}