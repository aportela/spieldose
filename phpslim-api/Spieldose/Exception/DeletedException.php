<?php

declare(strict_types=1);

namespace Spieldose\Exception;

/**
 * invalid parameters exception
 */
class DeletedException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
