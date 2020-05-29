<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\migration\libs\common
 * @category   CategoryName
 */

namespace open20\amos\core\migration\libs\common;

/**
 * Class MigrationCommon
 *
 * Common class for migrations libraries. There are common methods and utilities in this class.
 *
 * @package open20\amos\core\migration\libs\common
 */
class MigrationCommon
{
    /**
     * Useful to print a console message.
     * @param mixed $msg
     */
    public static function printConsoleMessage($msg)
    {
        print_r($msg);
        print_r("\n");
    }

    /**
     * Print console message for check structure.
     * @param array $authorization
     * @param string $msg
     */
    public static function printCheckStructureError($authorization, $msg)
    {
        self::printConsoleMessage($msg);
        self::printConsoleMessage($authorization);
    }
}
