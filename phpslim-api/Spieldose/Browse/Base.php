<?php

declare(strict_types=1);

namespace Spieldose\Browse;

abstract class Base implements \Spieldose\Browse\IBrowse
{
    public const int DEFAULT_RESULTS_PAGE = 32;

    protected \aportela\DatabaseWrapper\DB $dbh;
    protected array $fieldDefinitions;
    protected array $fieldCountDefinition;
    protected \aportela\DatabaseBrowserWrapper\Pager $pager;
    protected \aportela\DatabaseBrowserWrapper\Filter $filter;
    protected \aportela\DatabaseBrowserWrapper\Sort $sort;
    protected \aportela\DatabaseBrowserWrapper\Browser $browser;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh)
    {
        $this->dbh = $dbh;
    }

    public function __destruct() {}

    abstract function  browse(\aportela\DatabaseBrowserWrapper\Pager $pager, \aportela\DatabaseBrowserWrapper\Filter $filter, \aportela\DatabaseBrowserWrapper\Sort $sort): \aportela\DatabaseBrowserWrapper\BrowserResults;
}
