<?php

namespace Retech\Celest\SignMe\Exceptions;

use Throwable;

class WebhookValidationException extends \Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null) {
        $message = 'Error validating signature: ' . $message;
        parent::__construct($message, $code, $previous);
    }
}