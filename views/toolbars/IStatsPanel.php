<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\views\toolbars
 * @category   CategoryName
 */

namespace open20\amos\core\views\toolbars;


interface IStatsPanel
{

    public function getIcon();
    public function setIcon($icon);

    public function getLabel();
    public function setLabel($label);


    public function getDescription();
    public function setDescription($description);

    public function getCount();
    public function setCount($count);

    public function getUrl();
    public function setUrl($url);

    public function render($type);

}