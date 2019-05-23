<?php

namespace lispa\amos\core\bootstrap;

use yii\base\BootstrapInterface;

class DataBootstrap implements BootstrapInterface {

    public function bootstrap($app) {
        \yii\validators\Validator::$builtInValidators['string'] = [
            'class' => 'lispa\amos\core\validators\StringHtmlValidator',
        ];

        
    }

}
