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

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use Yii;

class PasswordInput extends \kartik\password\PasswordInput
{
    public function run()
    {

        $this->initLanguage();
        if ($this->hasModel()) {
            $this->name = ArrayHelper::remove(
                $this->options,
                'name',
                Html::getInputName($this->model, $this->attribute)
            );
            $this->value = $this->model[$this->attribute];
        }
        echo $this->getInput('passwordInput');
        if (empty($this->pluginOptions['inputTemplate'])
        ) {
            $this->pluginOptions['inputTemplate'] = $this->renderInputTemplate();
        }

        //change the label from "show password" to "hide"
        if(!isset( $this->pluginEvents["strength.toggle"] )) {

            $this->pluginEvents["strength.toggle"] = "function() { 
                $('.label-checkbox').each(function(){
                    $(this).toggle();
                });
             }";
        }

        $this->registerAssets();
        //parent::run();
    }

    protected function renderInputTemplate()
    {
        //TO DO: insert attribute for in label-checkbox for the accessibility
        $class = 'input-group';
        $labelCheckbox = '<label class="label-checkbox" for="">'.Yii::t('amoscore', 'Mostra password').'</label>
                          <label class="label-checkbox" style="display: none" for="">'.Yii::t('amoscore', 'Nascondi password').'</label>';
        $content = '{input}<span class="input-group-addon">{toggle}</span>';
        if ($this->size === 'lg' || $this->size === 'sm') {
            $class .= ' input-group-' . $this->size;
        }
        if ($this->togglePlacement === 'left') {
            $content = '<span class="input-group-addon">{toggle}</span>{input}';
        }

        return "<div class='{$class}'>{$labelCheckbox}{$content}</div>";
    }
}