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

class WidgetGraphic extends WidgetAbstract {

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
   * TBD - FRANZ ?!?!??!!?
   * 
   * @return string
   */
  public function getHtml() {
    return "############GRAPHIC!############";
  }

}
