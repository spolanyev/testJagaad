<?php
/**
 * @author Stanislav Polaniev <spolanyev@gmail.com>
 */

namespace App\Exception;

class InvalidApiResponseException extends \InvalidArgumentException
{
    public function __construct(
        string $message = 'Probably API response changed',
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
