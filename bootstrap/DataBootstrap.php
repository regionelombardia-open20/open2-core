<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    Open20Package
 * @category   CategoryName
 */

namespace open20\amos\core\bootstrap;

use yii\base\BootstrapInterface;

class DataBootstrap implements BootstrapInterface {

    public function bootstrap($app) {
        \yii\validators\Validator::$builtInValidators['string'] = [
            'class' => 'open20\amos\core\validators\StringHtmlValidator',
        ];

        
    }

}
