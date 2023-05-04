<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\session
 * @category   CategoryName
 */

namespace open20\amos\core\session;

use yii\web\DbSession as BaseDbSession;

class DBSession extends BaseDbSession
{
    
    public function regenerateID($deleteOldSession = false)
    {
        $ret = parent::regenerateID($deleteOldSession);
        $this->_forceRegenerateId = null;
        return $ret;
    }
}

