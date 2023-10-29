<?php

namespace Cis\Barcode\Exceptions;

use Exception;
use Throwable;

class CreateImageException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct(empty($message) ? 'Could not generate the image' : $message, $code, $previous);
    }
}