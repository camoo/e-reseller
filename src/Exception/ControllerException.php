<?php

declare(strict_types=1);

namespace App\Exception;

use Throwable;

final class ControllerException extends AppException
{
    public function __construct(?string $message = null, int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, [], null, null, [], $previous);
    }
}
