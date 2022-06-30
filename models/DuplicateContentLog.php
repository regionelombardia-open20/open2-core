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
 * Class DuplicateContentLog
 * This is the model class for table "duplicate_content_log".
 * @package open20\amos\core\models
 */
class DuplicateContentLog extends \open20\amos\core\models\base\DuplicateContentLog
{
    /**
     * @inheritdoc
     */
    public function representingColumn()
    {
        return [
            'model_classname',
            'source_model_id',
            'duplicate_model_id'
        ];
    }
}
