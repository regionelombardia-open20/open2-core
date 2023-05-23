<?php
namespace open20\amos\core\applications;

use yii\console\Application;

class ConsoleBoot extends AbstractBoot
{

    protected function createApplication($config)
    {
        return new Application($config);
    }
}

