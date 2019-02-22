<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    piattaforma-openinnovation
 * @category   CategoryName
 */

namespace lispa\amos\core\components;

use Yii;
use yii\base\Component;

/**
 * Class Runner - a component for running console command in yii2 web applications
 *
 *
 * Basic usage:
 * ```php
 * $output = '';
 * $runner = new Runner();
 * $runner->run('controller/action param1 param2 ...', $output);
 * echo $output; //prints the command output
 * ```
 *
 * Application component usage:
 * ```php
 * //you config file
 * 'components' => [
 *     'consoleRunner' => [
 *         'class' => 'lispa\amos\core\components\RunConsole'
 *     ]
 * ]
 * ```
 * ```php
 * //some application file
 * $output = '';
 * Yii::$app->consoleRunner->run('controller/action param1 param2 ...', $output);
 * echo $output; //prints the command output
 * ```
 */

class RunConsole extends Component{
    /**
     * @var string yii console application file that will be executed
     */
    public $yiiscript;
    /**
     * @var string path to php executable
     */    
    public $phpexec;
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        set_time_limit(0);
        if($this->yiiscript == null) {
            $this->yiiscript = "@app/../yii";
        }
    }
    /**
     * Runs yii console command
     *
     * @param $cmd command with arguments
     * @param string $output filled with the command output
     * @return int termination status of the process that was run
     */
    public function run($cmd, &$output = '')
    {
        $handler = popen($this->buildCommand($cmd), 'r');
        while(!feof($handler))
            $output .= fgets($handler);
        $output = trim($output);
        $status = pclose($handler);
        return $status;
    }
    /**
     * Builds the command string
     *
     * @param $cmd Yii command
     * @return string full command to execute
     */
    protected function buildCommand($cmd)
    {
        return $this->getPHPExecutable() . ' ' . Yii::getAlias($this->yiiscript) . ' ' . $cmd . ' 2>&1';
    }
    /**
     * If property $phpexec is set it will be used as php executable
     *
     * @return string path to php executable
     */
    public function getPHPExecutable()
    {
        if($this->phpexec) {
            return $this->phpexec;
        }
        return PHP_BINDIR . '/php';
    }
}
