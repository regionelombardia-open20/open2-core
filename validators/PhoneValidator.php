<?php

namespace open20\amos\core\validators;

use yii\validators\Validator;
use Exception;
use open20\amos\core\module\BaseAmosModule;

/**
 * Phone validator class that validates phone numbers
 * @property bool $international Verifica che il numero inizi per 00 oppure per +
 * @property array $allowedCharacters Un'array contenente tutti i caratteri permessi
 *          
 */
class PhoneValidator extends Validator
{
    /**
     * @var boolean $international
     */
    public $international = false;

    /**
     * @var array $allowedCharacters
     */
    public $allowedCharacters = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '+', '-', '/', '(', ')'];

    public function validateAttribute($model, $attribute)
    {
        $newValue = trim($model->$attribute);
        $valid    = false;
        if ($this->international == true) {
            if (strpos($newValue, '+') === 0) {
                $valid = $this->verifyNumber($newValue);
            } else if (strpos($newValue, '00') === 0) {
                $valid = $this->verifyNumber($newValue);
            } else {
                $this->addError($model, $attribute,
                    BaseAmosModule::t('amoscore',
                        'Phone number does not seem to be a valid phone number. Do you have to add +xx or 00 as prefix.'));
            }
        } else {
            $valid = $this->verifyNumber($newValue);
            if ($valid == false) {
                $this->addError($model, $attribute,
                    BaseAmosModule::t('amoscore', 'Phone number does not seem to be a valid phone number'));
            }
        }

        return $valid;
    }

    /**
     *
     */
    protected function verifyNumber($number)
    {
        $numberArray = str_split(trim($number));
        foreach ($numberArray as $v) {
            if (!in_array($v, $this->allowedCharacters)) {
                return false;
            }
        }
        return true;
    }

    public function clientValidateAttribute($model, $attribute, $view)
    {
        $required = $model->isAttributeRequired($attribute) == true ? 'true' : 'false';

        $nameField = trim(strip_tags($model->getAttributeLabel($attribute)));

        $error_msg = BaseAmosModule::t('amoscore', 'Numero di telefono non corretto, caratteri ammessi: 0123456789 ( + - / )');

        $error_msg_required = BaseAmosModule::t('amoscore', "$nameField non può essere vuoto");

        return <<<JS

        var phone_number = value;
		var required = "$required";
        if( phone_number == ''){
			if($required == true){
				messages.push( "$error_msg_required");
			} else {
            return '';
			}
        }
        if( ! /^[0-9+-/()]{3,16}$/.test(phone_number) ){
            messages.push( "$error_msg");
        }

        return true;
JS;
    }
}