<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\forms\editors\m2mWidget
 * @category   CategoryName
 */

namespace lispa\amos\core\forms\editors\m2mWidget;

/**
 * Class M2MEventsEnum
 * @package lispa\amos\core\forms\editors\m2mWidget
 */
class M2MEventsEnum
{
    /**
     * Events before and after associateM2m constants
     */
    const EVENT_BEFORE_ASSOCIATE_M2M = 'beforeAssociateM2m';
    const EVENT_AFTER_ASSOCIATE_M2M = 'afterAssociateM2m';
    const EVENT_BEFORE_DELETE_M2M = 'beforeDeleteM2m';
    const EVENT_AFTER_DELETE_M2M = 'afterDeleteM2m';
    const EVENT_BEFORE_CANCEL_ASSOCIATE_M2M = 'beforeCancelAssociateM2m';
    const EVENT_AFTER_CANCEL_ASSOCIATE_M2M = 'afterCancelAssociateM2m';
    const EVENT_BEFORE_MANAGE_ATTRIBUTES_M2M = 'beforeManageAttributesM2m';
    const EVENT_AFTER_MANAGE_ATTRIBUTES_M2M = 'afterManageAttributesM2m';
    const EVENT_BEFORE_ASSOCIATE_ONE2MANY = 'beforeAssociateOne2Many';
    const EVENT_AFTER_ASSOCIATE_ONE2MANY = 'afterAssociateOne2Many';
    const EVENT_BEFORE_RENDER_ASSOCIATE_ONE2MANY = 'beforeRenderAssociateOne2Many';
}
