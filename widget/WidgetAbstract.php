<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\widget
 * @category   CategoryName
 */

namespace lispa\amos\core\widget;

use yii\base\Widget;

abstract class WidgetAbstract extends Widget
{
    public $label;
    public $description;
    public $code;
    public $moduleName;
    public $widgetPermission = null;
    public $isVisible        = false;
    public $children         = [];

    /**
     * The class name define custom size
     * @var string $classFullSize
     */
    public $classFullSize;

    public function isVisible()
    {
        if ($return = \Yii::$app->getUser()->can($this->getWidgetPermission())) {
            return true;
        } else {
            //pr($this->getWidgetPermission() ,'NON PRESENTE!');
            return false;
        }
    }

    /**
     * @return string
     */
    public function getWidgetPermission()
    {
        return $this->widgetPermission;
    }

    /**
     * @param string $widgetPermission
     */
    public function setWidgetPermission($widgetPermission)
    {
        $this->widgetPermission = $widgetPermission;
    }

    public function init()
    {
        parent::init();
        $permissionWidgetName = get_called_class();
        $this->setWidgetPermission($permissionWidgetName);
    }

    /**
     * @return mixed
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * @param mixed $moduleName
     */
    public function setModuleName($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * This method set the class name define custom size
     * @param string $customSize 
     */
    public function setClassFullSize($classFullSize)
    {
        $this->classFullSize = $classFullSize;
    }

    /**
     * @return string Return the class name define custom size
     */
    public function getClassFullSize()
    {
        return $this->classFullSize;
    }
}