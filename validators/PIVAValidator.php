<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\validators
 * @category   CategoryName
 */

namespace lispa\amos\core\validators;

use yii\validators\Validator;

/**
 * Description of PIVAValidator
 *
 */
class PIVAValidator extends Validator
{

    /**
     * 
     * @param \backend\components\ActiveRecord $model
     * @param string $attribute
     * @return boolean
     */
    function validateAttribute($model, $attribute)
    {
        $pi = $model->$attribute;

        if (strlen($pi) != 11) {
            $this->addError($model, $attribute, 'Partita Iva non valida - lunghezza non conforme');
            return false;
        }
        if (!preg_match("/^[0-9][0-9]*$/", $pi)) {
            $this->addError($model, $attribute, 'Partita Iva non valida - presenta valori non numerici');
            return false;
        }
        $s = 0;
        for ($i = 0; $i <= 9; $i += 2) {
            $s += ord($pi[$i]) - ord('0');
        }
        for ($i = 1; $i <= 9; $i += 2) {
            $c = 2 * ( ord($pi[$i]) - ord('0') );
            if ($c > 9)
                $c = $c - 9;
            $s += $c;
        }
        if (( 10 - $s % 10 ) % 10 != ord($pi[10]) - ord('0')) {
            $this->addError($model, $attribute, \Yii::t('app', 'Partita Iva non valida') );
            return false;
        }

        return true;
    }


    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     * @param \yii\web\View $view
     * @return string
     * P.Iva validator client side
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        $error_msg = \Yii::t('app', 'Partita Iva non valida');
        $error_format_msg = \Yii::t('app', 'La partita IVA deve contenere 11 cifre');
        return <<<JS
            var pi = value;
                
            if( pi == '' ){
                return true;
            }  
            
            if( ! /^[0-9]{11}$/.test(pi) ){
                messages.push("$error_format_msg");
            }
            var s = 0;
            for( i = 0; i <= 9; i += 2 ){
                s += pi.charCodeAt(i) - '0'.charCodeAt(0);
            }
            for(var i = 1; i <= 9; i += 2 ){
                var c = 2*( pi.charCodeAt(i) - '0'.charCodeAt(0) );
                if( c > 9 )  c = c - 9;
                s += c;
            }
            var atteso = ( 10 - s%10 )%10;
            if( atteso != pi.charCodeAt(10) - '0'.charCodeAt(0) ){
                messages.push("$error_msg");
            }
            return true;
JS;
    }

// validate
}
//TSCGSI46B27L483C