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

use yii\db\ActiveRecord;
use yii\validators\Validator;

/**
 * Description of CFValidator
 *
 */
class CFValidator extends Validator
{

    /**
     * 
     * @param ActiveRecord $model
     * @param string $attribute
     * @return boolean
     */


    public function validateAttribute($model, $attribute)
    {
        $theVar = strtoupper($model->$attribute);
        if (strlen($theVar) != 16) {
            $this->addError($model, $attribute, 'Codice Fiscale non valido - lunghezza non conforme');
            return false;
        }


        if (!preg_match("/^[A-Z0-9]+$/i", $theVar)) {
            $this->addError($model, $attribute, 'Codice Fiscale non valido');
            return false;
        }

        $s = 0;
        for ($i = 1; $i <= 13; $i += 2) {
            $c = $theVar[$i];
            if ('0' <= $c && $c <= '9') {
                $s += ord($c) - ord('0');
            } else {
                $s += ord($c) - ord('A');
            }
        }
        for ($i = 0; $i <= 14; $i += 2) {
            $c = $theVar[$i];
            switch ($c) {
                case '0': $s += 1;
                    break;
                case '1': $s += 0;
                    break;
                case '2': $s += 5;
                    break;
                case '3': $s += 7;
                    break;
                case '4': $s += 9;
                    break;
                case '5': $s += 13;
                    break;
                case '6': $s += 15;
                    break;
                case '7': $s += 17;
                    break;
                case '8': $s += 19;
                    break;
                case '9': $s += 21;
                    break;
                case 'A': $s += 1;
                    break;
                case 'B': $s += 0;
                    break;
                case 'C': $s += 5;
                    break;
                case 'D': $s += 7;
                    break;
                case 'E': $s += 9;
                    break;
                case 'F': $s += 13;
                    break;
                case 'G': $s += 15;
                    break;
                case 'H': $s += 17;
                    break;
                case 'I': $s += 19;
                    break;
                case 'J': $s += 21;
                    break;
                case 'K': $s += 2;
                    break;
                case 'L': $s += 4;
                    break;
                case 'M': $s += 18;
                    break;
                case 'N': $s += 20;
                    break;
                case 'O': $s += 11;
                    break;
                case 'P': $s += 3;
                    break;
                case 'Q': $s += 6;
                    break;
                case 'R': $s += 8;
                    break;
                case 'S': $s += 12;
                    break;
                case 'T': $s += 14;
                    break;
                case 'U': $s += 16;
                    break;
                case 'V': $s += 10;
                    break;
                case 'W': $s += 22;
                    break;
                case 'X': $s += 25;
                    break;
                case 'Y': $s += 24;
                    break;
                case 'Z': $s += 23;
                    break;
            }
        }
        if (chr($s % 26 + ord('A')) != $theVar[15]) {
            $this->addError($model, $attribute, \Yii::t('app', 'Codice Fiscale non valido') );
            return false;
        }
        return true;
    }

    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     * @param \yii\web\View $view
     * @return string
     * Validator CF client side
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        $error_msg = \Yii::t('app', 'Codice Fiscale non valido');
        $error_format_msg = \Yii::t('app', 'Il codice fiscale deve contenere 16 tra lettere e cifre');
        return <<<JS
        
        var cf = value.toUpperCase();
        
        if( cf == '' ){
            return '';
        }  
        if( ! /^[0-9A-Z]{16}$/.test(cf) ){
            messages.push( "$error_format_msg");
        }
        var map = [1, 0, 5, 7, 9, 13, 15, 17, 19, 21, 1, 0, 5, 7, 9, 13, 15, 17, 19, 21, 2, 4, 18, 20, 11, 3, 6, 8, 12, 14, 16, 10, 22, 25, 24, 23];
        
        var s = 0;
        for(var i = 0; i < 15; i++){
            var c = cf.charCodeAt(i);
            if( c < 65 ){
                c = c - 48;
            }
            else{
                c = c - 55;
            }
            if( i % 2 == 0 ){
                s += map[c];
            }
            else{
                s += c < 10? c : c - 10;
            }
        }
        var atteso = String.fromCharCode(65 + s % 26);
        if( atteso != cf.charAt(15) ){
             messages.push( "$error_msg");
        }
        return true;
JS;


    }

// validate
}
