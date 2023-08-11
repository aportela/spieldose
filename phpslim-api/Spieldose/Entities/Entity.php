<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Entity
{
    protected $dbh = null;
    public $mbId = null;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, string $mbId = "")
    {
        $this->dbh = $dbh;
        $this->mbId = $mbId;
    }

    public function __destruct()
    {
    }
}
