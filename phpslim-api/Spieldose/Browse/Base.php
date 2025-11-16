<?php

declare(strict_types=1);

namespace Spieldose\Browse;

abstract class Base implements \Spieldose\Browse\IBrowse
{
    protected \aportela\DatabaseWrapper\DB $dbh;
    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
    }

    public function __destruct() {}

    abstract public function browse(): \aportela\DatabaseBrowserWrapper\BrowserResults;
}
