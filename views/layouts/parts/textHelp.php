<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\views\layouts\parts
 * @category   CategoryName
 */

use yii\helpers\FileHelper;

/* @var $this \yii\web\View */

if (array_key_exists('textHelp', $this->params) && isset($this->params['textHelp']['filename'])) {
//    var_dump($this->params['textHelp']);
    echo $this->renderPhpFile(
        FileHelper::localize($this->context->getViewPath() . DIRECTORY_SEPARATOR . 'help' . DIRECTORY_SEPARATOR . $this->params['textHelp']['filename'] . '.php'),
        $this->params['textHelp']
    );
}
