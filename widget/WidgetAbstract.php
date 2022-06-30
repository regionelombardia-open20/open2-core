<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\widget
 * @category   CategoryName
 */

namespace open20\amos\core\widget;

use yii\base\Widget;

abstract class WidgetAbstract extends Widget {

  const
    ENGINE_COLUMNS = 'columns',
    ENGINE_ROWS = 'rows';

  public 
    $label,
    $description,
    $code,
    $moduleName,
    $widgetPermission = null,
    $isVisible = false,
    $children = [],
    $engine = self::ENGINE_COLUMNS,
    $classFullSize;                     // The class name define custom size

  /**
   * @inheritdoc 
   */
  public function init() {
    parent::init();
    
    $permissionWidgetName = get_called_class();
    $this->setWidgetPermission($permissionWidgetName);
    if (!empty(\Yii::$app->params['dashboardEngine']) && in_array(\Yii::$app->params['dashboardEngine'], [self::ENGINE_COLUMNS, self::ENGINE_ROWS])) {
      $this->setEngine(\Yii::$app->params['dashboardEngine']);
    }
  }

  /**
   * TDB - FRANZ - Investigate this ->can() function
   * 
   * @return boolean
   */
  public function isVisible() {
    return \Yii::$app->getUser()->can($this->getWidgetPermission());

  }

  /**
   * @return string
   */
  public function getEngine() {
    return $this->engine;
  }

  /**
   * @param $engine
   */
  public function setEngine($engine) {
    $this->engine = $engine;
  }

  /**
   * @return string
   */
  public function getWidgetPermission() {
    return $this->widgetPermission;
  }

  /**
   * @param string $widgetPermission
   */
  public function setWidgetPermission($widgetPermission) {
    $this->widgetPermission = $widgetPermission;
  }

  /**
   * @return mixed
   */
  public function getModuleName() {
    return $this->moduleName;
  }

  /**
   * @param mixed $moduleName
   */
  public function setModuleName($moduleName) {
    $this->moduleName = $moduleName;
  }

  /**
   * @return mixed
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * @param mixed $description
   */
  public function setDescription($description) {
    $this->description = $description;
  }

  /**
   * @return mixed
   */
  public function getCode() {
    return $this->code;
  }

  /**
   * @param mixed $code
   */
  public function setCode($code) {
    $this->code = $code;
  }

  /**
   * @return mixed
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * @param mixed $label
   */
  public function setLabel($label) {
    $this->label = $label;
  }

  /**
   * This method set the class name define custom size
   * @param string $customSize 
   */
  public function setClassFullSize($classFullSize) {
    $this->classFullSize = $classFullSize;
  }

  /**
   * @return string Return the class name define custom size
   */
  public function getClassFullSize() {
    return $this->classFullSize;
  }

}