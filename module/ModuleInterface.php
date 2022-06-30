<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\module
 * @category   CategoryName
 */

namespace open20\amos\core\module;

interface ModuleInterface
{
    public function getWidgetIcons();

    public function getWidgetGraphics();

    public static function getModuleName();

}