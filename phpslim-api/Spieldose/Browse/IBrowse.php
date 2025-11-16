<?php

declare(strict_types=1);

namespace Spieldose\Browse;

interface IBrowse
{
    public function browse(int $currentPage, int $resultsPage): \aportela\DatabaseBrowserWrapper\BrowserResults;
}
