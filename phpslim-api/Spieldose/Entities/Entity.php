<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Entity
{
    /**
     * @var string
     */
    public $mbId;

    public function __construct(protected \aportela\DatabaseWrapper\DB $dbh, string $mbId = "")
    {
        $this->mbId = $mbId;
    }
}
