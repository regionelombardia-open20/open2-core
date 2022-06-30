<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\helpers
 * @category   CategoryName
 */

namespace open20\amos\core\helpers;


class Crumb
{
    public $label = '';
    public $title = '';
    public $url = '';
    public $controller = null;
    public $model = null;
    public $module = null;
    public $action = null;
    public $route = null;
    public $params = null;
    public $remove_action = null;
    public $visible = true;
    public $isIndex = false;

    /**
     * @return array
     */
    public function asArray()
    {
        return (array)$this;
    }

    /**
     * @param Crumb $crumb
     * @return bool
     */
    public function equals(Crumb $crumb)
    {
        return ($this->label == $crumb->label && $this->controller == $crumb->controller);
    }
}