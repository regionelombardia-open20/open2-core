<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    Open20Package
 * @category   CategoryName
 */

namespace open20\amos\core\validators;

use yii\validators\Validator;
use Exception;

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
                    \Yii::t('amoscore',
                        'Phone number does not seem to be a valid phone number. Do you have to add +xx or 00 as prefix.'));
            }
        } else {
            $valid = $this->verifyNumber($number);
            if ($valid == false) {
                $this->addError($model, $attribute,
                    \Yii::t('amoscore', 'Phone number does not seem to be a valid phone number'));
            }
        }

        return $valid;
    }

    /**
     *
     */
    protected function verifyNumber($number)
    {
        $numberArray = str_split($number);
        foreach ($numberArray as $v) {
            if (!in_array($v, $this->allowedCharacters)) {
                return false;
            }
        }
        return true;
    }
}