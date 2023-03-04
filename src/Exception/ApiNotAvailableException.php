<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Exception;

class ApiNotAvailableException extends \UnexpectedValueException
{
    public function __construct(
        string $message = 'Probably API not responding',
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
