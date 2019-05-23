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

use lispa\amos\core\icons\AmosIcons;

class WidgetIcon extends WidgetAbstract {

  public
    $url,
    $post,
    $icon = 'linmodulo',
    $iconFramework = 'dash',
    $namespace,
    $classLi = [],
    $classA = [],
    $classSpan = ['color-primary'],
    $targetUrl = '',
    $bulletCount = '',
    $dataPjaxZero = '',
    $attributes = ''          // @var string $attributes - additional attributes for html tag <a>
  ;

  /**
   * @inheritdoc
   */
  public function init() {
    parent::init();
  }

  /**
   * 
   * @return string
   */
  public function run() {
    if ($this->isVisible()) {
      return $this->getHtml();
    }
    
    return '';
  }
  
  /**
   * 
   * @param type $module
   */
  public function checkScope($module = null) {
    $moduleCwh = \Yii::$app->getModule('cwh');
    
    if (isset($moduleCwh)) {
      $scope = $moduleCwh->getCwhScope();
      if (!empty($scope)) {
        if (isset($scope[$module])) {
          return $scope[$module];
        }
      }
    }
    
    return false;
  }
  
  /**
   * 
   * @return type
   */
  public function getHtml() {
    $controller = \Yii::$app->controller;
    $moduleL = \Yii::$app->getModule('layout');
    
    if (!empty($moduleL)) {
      $assetBundle = \lispa\amos\layout\assets\BaseAsset::register($controller->getView());
    } else {
      $assetBundle = \lispa\amos\core\views\assets\AmosCoreAsset::register($controller->getView());
    }

    $view = '@vendor/lispa/amos-core/widget/views/icon';
    if ($this->getEngine() == WidgetAbstract::ENGINE_ROWS) {
      $view = '@vendor/lispa/amos-core/widget/views/icon_rows';
    }
    
    $params = [
      'asset' => $assetBundle,
      'widget' => $this
    ];
    
    return $this->render($view, $params);
  }

  /**
   * 
   * @return type
   */
  public function getOptions() {
    return [
      'isVisible' => $this->isVisible(),
      'label' => $this->getLabel(),
      'description' => $this->getDescription(),
      'code' => $this->getCode(),
      'url' => $this->getUrl(),
      'post' => $this->getPost(),
      'moduleName' => $this->getModuleName(),
      'icon' => $this->getIcon(),
      'namespace' => $this->getNamespace(),
      'iconFramework' => $this->getIconFramework(),
      'classSpan' => $this->getClassSpan(),
      'attributes' => $this->getAttributes()
    ];
  }

  /**
   * @return string
   */
  public function getBulletCount() {
    return $this->bulletCount;
  }

  /**
   * @param string $bulletCount
   */
  public function setBulletCount($bulletCount) {
    $this->bulletCount = $bulletCount;
  }

//  /**
//   *
//   * @param string $bulletCount
//   * @return string
//   */
//  protected function getNewsBulletCount($bulletCount = '') {
//    return $bulletCount;
//    /* try {
//      $className = $this->namespace;
//      $widget    = \lispa\amos\dashboard\models\AmosWidgets::findOne(['classname' => $className]);
//      if (!empty($widget) && $widget->hasMethod('isNews')) {
//      $bulletCount = $widget->isNews() ? \Yii::t('amoscore', 'NEW') : $bulletCount;
//      }
//      return $bulletCount;
//      } catch (Exception $ex) {
//      return $bulletCount;
//      } */
//  }

  /**
   * @return mixed
   */
  public function getPost() {
    return $this->post;
  }

  /**
   * @return mixed
   */
  public function setPost($post) {
    $this->post = $post;
  }

  /**
   * @return mixed
   */
  public function getTargetUrl() {
    return $this->targetUrl;
  }

  /**
   * @param mixed $targetUrl
   */
  public function setTargetUrl($targetUrl) {
    $this->targetUrl = $targetUrl;
  }

  /**
   * @return array
   */
  public function getClassLi() {
    return $this->classLi;
  }

  /**
   * @param array $classLi
   */
  public function setClassLi($classLi) {
    $this->classLi = $classLi;
  }

  /**
   * @return array
   */
  public function getClassA() {
    return $this->classA;
  }

  /**
   * @param array $classA
   */
  public function setClassA($classA) {
    $this->classA = $classA;
  }

  /**
   * @return mixed
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * @param mixed $url
   */
  public function setUrl($url) {
    $this->url = $url;
  }

  /**
   * @return mixed
   */
  public function getIcon() {
    return $this->icon;
  }

  /**
   * @param mixed $icon
   */
  public function setIcon($icon) {
    $this->icon = $icon;
  }

  /**
   * @return mixed
   */
  public function getNamespace() {
    return $this->namespace;
  }

  /**
   * @param mixed $namespace
   */
  public function setNamespace($namespace) {
    $this->namespace = $namespace;
  }

  /**
   * @return mixed
   */
  public function getIconFramework() {
    return $this->iconFramework;
  }

  /**
   * @param mixed $iconFramework
   */
  public function setIconFramework($iconFramework) {
    $this->iconFramework = $iconFramework;
  }

  /**
   * @return array
   */
  public function getClassSpan() {
    return $this->classSpan;
  }

  /**
   * @param array $classSpan
   */
  public function setClassSpan($classSpan) {
    $this->classSpan = $classSpan;
  }

  /**
   * @return string
   */
  public function getDataPjaxZero() {
    return $this->dataPjaxZero;
  }

  /**
   * @param string $dataPjaxZero
   */
  public function setDataPjaxZero($dataPjaxZero) {
    $this->dataPjaxZero = $dataPjaxZero;
  }

  /**
   * @return string
   */
  public function getAttributes() {
    return $this->attributes;
  }

  /**
   * @param string $attributes
   */
  public function setAttributes($attributes) {
    $this->attributes = $attributes;
  }

  /**
   *
   */
  public function enableDashboardModal() {
    $this->classA[] = 'open-modal-dashboard';
  }

}
