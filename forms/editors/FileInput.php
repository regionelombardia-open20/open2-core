<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\forms\editors
 * @category   CategoryName
 */

namespace lispa\amos\core\forms\editors;

use dosamigos\fileinput\FileInput as YiiFileInput;
//use pendalf89\filemanager\widgets\FileInput as YiiFileInput;
use yii\helpers\ArrayHelper;

class FileInput extends YiiFileInput
{

    public $directInput = false;
   
    public function init()
    {
        parent::init();
      /*  $this->buttonTag = 'button';
        $this->buttonName = 'Sfoglia';
        $this->buttonOptions = ArrayHelper::merge($this->buttonOptions, ['class' => 'btn btn-success']);
        $this->resetButtonName = 'Rimuovi';
        $this->resetButtonOptions = ArrayHelper::merge($this->resetButtonOptions, ['class' => 'btn btn-danger']);
        $this->options = ArrayHelper::merge($this->options, ['class' => 'form-control']);
        $this->template = '<span class="hide">{input}</span>{button} {reset-button}';
        $this->thumb = 'medium';*/
        $this->customView = $this->getViewPath() . '/imageField.php';       
    }

    public function run()
    {
        if ($this->directInput) {
            return "######FILE INPUT######";
        } else {
            return parent::run();
        }
    }

}