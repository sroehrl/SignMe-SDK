<?php

namespace Retech\Celest\SignMe\Exceptions;

use Exception;
use Throwable;

class ConnectionException extends Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null) {
        $message = 'Connection error: ' . $message;
        parent::__construct($message, $code, $previous);
    }
}