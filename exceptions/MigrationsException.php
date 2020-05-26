<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package open20\amos\core\exceptions
 * @category CategoryName
 * @author Lombardia Informatica S.p.A.
 */

namespace open20\amos\core\exceptions;

/**
 * Class MigrationsException
 * @package open20\amos\core\exceptions
 */
class MigrationsException extends \Exception
{
    /**
     * @inheritdoc
     */
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n{$this->getFile()}:{$this->getLine()}\n";
    }
}
