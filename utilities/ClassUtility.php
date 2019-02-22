<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\utilities
 * @category   CategoryName
 */

namespace lispa\amos\core\utilities;


use yii\base\Exception;
use yii\base\Object;

class ClassUtility extends Object
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
}