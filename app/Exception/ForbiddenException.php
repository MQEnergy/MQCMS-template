<?php

declare(strict_types=1);

namespace App\Exception;

use App\Constants\ErrorCode;
use Throwable;

class ForbiddenException extends BusinessException
{
    public function __construct(string $message = null, Throwable $previous = null)
    {
        parent::__construct(ErrorCode::FORBIDDEN, $message, $previous);
    }
}