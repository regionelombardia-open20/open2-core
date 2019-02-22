<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\core\module
 * @category   CategoryName
 */

namespace lispa\amos\core\module;

interface ModuleInterface
{
    public function getWidgetIcons();

    public function getWidgetGraphics();

    public static function getModuleName();

}