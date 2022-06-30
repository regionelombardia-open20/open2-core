<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\behaviors
 * @category   CategoryName
 */

namespace open20\amos\core\behaviors;

use yii\base\Behavior;
use yii\base\Exception;

/**
 * Class VersionableBehaviour
 * @package open20\amos\core\behaviors
 */
class VersionableBehaviour extends Behavior
{

    public $versionAttribute = 'version';
    public $versionTable = null;

    /**
     * Initialize behaviors
     * @throws Exception
     */
    public function init()
    {
        parent::init();

        if (!$this->versionTable) {
            throw new Exception(\Yii::t('amoscore', 'Version table not defined'));
        }


    }

}