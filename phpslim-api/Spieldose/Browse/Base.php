<?php

declare(strict_types=1);

namespace Spieldose\Browse;

abstract class Base implements \Spieldose\Browse\IBrowse
{
    public const int DEFAULT_RESULTS_PAGE = 32;

    protected array $fieldDefinitions;

    protected array $fieldCountDefinition;

    protected \aportela\DatabaseBrowserWrapper\Pager $pager;

    protected \aportela\DatabaseBrowserWrapper\Filter $filter;

    protected \aportela\DatabaseBrowserWrapper\Sort $sort;

    protected \aportela\DatabaseBrowserWrapper\Browser $browser;

    public function __construct(protected \aportela\DatabaseWrapper\DB $dbh) {}

    abstract public function browse(\aportela\DatabaseBrowserWrapper\Pager $pager, \aportela\DatabaseBrowserWrapper\Filter $filter, \aportela\DatabaseBrowserWrapper\Sort $sort): \aportela\DatabaseBrowserWrapper\BrowserResults;
}
