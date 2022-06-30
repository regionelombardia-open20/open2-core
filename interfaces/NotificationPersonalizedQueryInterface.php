<?php

namespace open20\amos\core\interfaces;

use open20\amos\core\user\User;
use yii\db\ActiveQuery;

interface NotificationPersonalizedQueryInterface
{

    /**
     * Method used to get the notification records
     * @param $user User
     * @param $cwhActiveQuery ActiveQuery
     * @return ActiveQuery
     */
    public function getNotificationQuery($user ,$cwhActiveQuery);

    /**
     * @param $user User
     * @param $cwhActiveQuery ActiveQuery
     * @return ActiveQuery
     */
    public function canSaveNotification();
}