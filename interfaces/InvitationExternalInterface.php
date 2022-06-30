<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\interfaces
 * @category   CategoryName
 */

namespace open20\amos\core\interfaces;

/**
 * Interface InvitationExternalInterface
 * @package open20\amos\core\interfaces
 */
interface InvitationExternalInterface
{
    /**
     * This method make the association from the specified context model and the specified user.
     * @param int $userId
     * @param mixed $modelId
     * @return bool
     */
    public function addUserContextAssociation($userId, $modelId);
}
