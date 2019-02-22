<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\record
 * @category   CategoryName
 */

namespace lispa\amos\core\record;

use backend\modules\admin\models\UserProfile;
use \lispa\amos\core\record\Record;
use \bedezign\yii2\audit\AuditTrailBehavior;
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