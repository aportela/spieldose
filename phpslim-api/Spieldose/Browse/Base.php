<?php

declare(strict_types=1);

namespace Spieldose\Browse;

abstract class Base implements \Spieldose\Browse\IBrowse
{
    public const int DEFAULT_RESULTS_PAGE = 32;

    protected \aportela\DatabaseWrapper\DB $dbh;
    protected \Psr\Log\LoggerInterface $logger;
    private array $fieldDefinitions;
    private array $fieldCountDefinition;
    private \aportela\DatabaseBrowserWrapper\Sort $sort;
    private \aportela\DatabaseBrowserWrapper\Filter $filter;
    private \aportela\DatabaseBrowserWrapper\Browser $browser;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
    }

    public function __destruct() {}

    abstract public function browse(\aportela\DatabaseBrowserWrapper\Pager $pager): \aportela\DatabaseBrowserWrapper\BrowserResults;
}
