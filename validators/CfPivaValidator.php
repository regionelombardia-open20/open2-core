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
use lispa\amos\core\validators\CFValidator;
use lispa\amos\core\validators\PIVAValidator;

/**
 * Description of CfPivaValidator
 *
 */
class CfPivaValidator extends Validator
{
    
    /**
     * 
     * @param \backend\components\ActiveRecord $model
     * @param string $attribute
     * @return type
     */
    function validateAttribute($model, $attribute)
    {
        $error = true;

        if(is_numeric ($model->$attribute)){
            // check Partita IVA
            $PIVA = new PIVAValidator();
            $error = $PIVA->validateAttribute($model, $attribute);
        }else{
            // check Codice Fiscale
            $CF = new CFValidator();
            $error = $CF->validateAttribute($model, $attribute);
        }
        
        if($error === false) {
            return;
        }else{
            $model->clearErrors($attribute);
        }
    }

    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     * @param \yii\web\View $view
     * @return string
     * Validator CF/PIVA client side
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        //load the js code for P.Iva validation
        $PIVA = new PIVAValidator();
        $validatePivaJs = $PIVA->clientValidateAttribute($model, $attribute, $view);

        //load the js code for CF validation
        $CF = new CFValidator();
        $validateCfJs = $CF->clientValidateAttribute($model, $attribute, $view);

        return <<<JS
            function isNumeric(n) {
                return !isNaN(parseFloat(n)) && isFinite(n);
            }
            
            //if is numeric use the js code for validate P.IVA, otherwise use tje js for the CF
            if( isNumeric(value) ){
                $validatePivaJs;
            }else{
                $validateCfJs;
                
            }
JS;
    }


}
