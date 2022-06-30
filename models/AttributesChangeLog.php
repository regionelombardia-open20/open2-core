<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\models
 * @category   CategoryName
 */

namespace open20\amos\core\models;

/**
 * Class AttributesChangeLog
 * This is the model class for table "attributes_change_log".
 * @package open20\amos\core\models
 */
class AttributesChangeLog extends \open20\amos\core\models\base\AttributesChangeLog
{
    /**
     * @inheritdoc
     */
    public function representingColumn()
    {
        return [
            'model_classname',
            'model_id',
            'model_attribute',
            'old_value',
            'new_value',
        ];
    }
}
