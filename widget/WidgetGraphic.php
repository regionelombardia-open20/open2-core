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
