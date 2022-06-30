<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\record
 * @category   CategoryName
 */

namespace open20\amos\core\record;

use backend\modules\admin\models\UserProfile;
use \open20\amos\core\record\Record;
use \open20\amos\audit\AuditTrailBehavior;
use \yii\db\ActiveQuery;
use \yii\helpers\ArrayHelper;

class AmosRecordAudit extends Record
{
    public function behaviors()
    {

        $behaviorsParent = parent::behaviors();

        $behaviors = [
            'auditTrailBehavior' => [
                'class' => AuditTrailBehavior::className()
            ],
        ];

        return ArrayHelper::merge($behaviorsParent, $behaviors);
    }
}