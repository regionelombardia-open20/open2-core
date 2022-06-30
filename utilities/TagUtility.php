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

use open20\amos\core\exceptions\AmosException;
use open20\amos\core\models\TagNotification;
use open20\amos\core\module\BaseAmosModule;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Json;

/**
 * Class MapsUtility
 * @package open20\amos\core\utilities
 */
class TagUtility extends BaseObject
{

    /**
     * Sets tag notification as read for a specific user.
     * @param int $userId - User ID
     * @param string $contextClassName - Context class; if not set, it'll flag all tag notifications for the user as read.
     * @param int $contextId - Context ID; if not set, it'll flag all tag notifications for the user (and the specified class, if applicable) as read.
     */
    public static function setNotificationsAsRead($userId, $contextClassName = null, $contextId = null)
    {
        // Avoid unstable state, by blocking query if contextClassName is null and contextId is not null
        // (would delete values based only on the class ID and not class name)
        if (empty($contextClassName) && !empty($contextId)) throw new AmosException(BaseAmosModule::t('amoscore', 'Cannot set tag notifications as read with an empty context class and non-empty context ID.'));

        $filters = ['and', ['user_id' => $userId]];

        if (!empty($contextClassName)) $filters[] = ['context_model_class_name' => $contextClassName];
        if (!empty($contextId)) $filters[] = ['context_model_id' => $contextId];

        try {
            TagNotification::updateAll(['read' => true], $filters);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    public static function setNotificationAsRead($id) {
        try {
            $notification = TagNotification::findOne($id);
            $notification->read = true;
            $notification->save(false);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
