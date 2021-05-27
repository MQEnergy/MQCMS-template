<?php

declare(strict_types=1);

namespace App\Exception;

use App\Constants\ErrorCode;
use Throwable;

class BadRequestException extends BusinessException
{
    public function __construct(string $message = null, Throwable $previous = null)
    {
        parent::__construct(ErrorCode::BAD_REQUEST, $message, $previous);
    }
}