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

class WidgetGraphic extends WidgetAbstract
{
    public function run()
    {
        if ($this->isVisible()) {
            return $this->getHtml();
        } else {
            return '';
        }
    }

    public function getHtml()
    {
        return "############GRAPHIC!############";
    }


}