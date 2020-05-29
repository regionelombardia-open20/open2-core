<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\utilities
 * @category   CategoryName
 */

namespace open20\amos\core\utilities;


use yii\base\Exception;
use yii\base\BaseObject;

class ClassUtility extends BaseObject
{

    /**
     * @param $classname
     * @return bool
     */
    public static function classExist($classname){
        $boolean = false;
        try {
            $boolean = class_exists($classname);
        }catch(Exception $ex){

        }
        return $boolean;
    }
    
    /**
     * 
     * @param type $obj
     * @param type $name
     * @param type $checkVars
     * @return type
     */
    public static function objectHasProperty($obj, $name, $checkVars = true)
    {
        $ret = false;
        
        if(is_object($obj))
        {
            $ret = $obj->hasProperty($name, $checkVars);
        }
        return $ret;
    }
}