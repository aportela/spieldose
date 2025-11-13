<?php

declare(strict_types=1);

namespace Spieldose\Exception;

/**
 * "unauthorized" custom exception (operation fails due not logged session)
 */
class UnauthorizedException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
