<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\giiamos\crud\providers
 * @category   CategoryName
 */

namespace lispa\amos\core\giiamos\crud\providers;

class DateTimeProvider extends \schmunk42\giiant\generators\crud\providers\extensions\DateTimeProvider
{


    public function activeField($attribute)
    {
        $column = $this->generator->getTableSchema()->columns[$attribute];

        switch ($column->type) {
            case 'datetime':
            case 'timestamp':
                $this->generator->requires[] = 'kartik\widgets\DateTimePicker;';
                /*return <<<EOS
\$form->field(\$model, '{$column->name}')->widget(DateTimePicker::classname(), [
	'options' => ['placeholder' => Yii::t('{$this->generator->messageCategory}','Inserisci un orario ...')],
	'pluginOptions' => [
		'autoclose' => true
	]
])
EOS;*/
                return <<<EOS
\$form->field(\$model, '{$column->name}')->widget(DateTimePicker::classname(), [
	'options' => ['placeholder' => Yii::t('amoscore','Set time')],
	'pluginOptions' => [
		'autoclose' => true
	]
])
EOS;
                break;
            case 'date':
                $this->generator->requires[] = 'kartik\datecontrol\DateControl';
                return <<<EOS
\$form->field(\$model, '{$column->name}')->widget(DateControl::classname(), [
    'displayFormat' => 'dd/MM/yyyy',
    'saveFormat' => 'yyyy-MM-dd',
    'autoWidget' => false,
    'widgetClass' => 'yii\widgets\MaskedInput',
    'options' => [
        'mask' => '99/99/9999'
    ],
])
EOS;
                break;
            default:
                return null;
        }
    }

} 
