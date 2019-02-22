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

if (array_key_exists('help', $this->params) && isset($this->params['help']['filename'])) {
    echo $this->renderPhpFile(FileHelper::localize($this->context->getViewPath() . DIRECTORY_SEPARATOR . 'help' . DIRECTORY_SEPARATOR . $this->params['help']['filename'] . '.php'));
}

if (array_key_exists('intro', $this->params) && isset($this->params['intro']['filename'])) {
    echo $this->renderPhpFile(FileHelper::localize($this->context->getViewPath() . DIRECTORY_SEPARATOR . 'intro' . DIRECTORY_SEPARATOR . $this->params['intro']['filename'] . '.php'));
}
