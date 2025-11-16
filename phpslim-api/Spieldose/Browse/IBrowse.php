<?php

declare(strict_types=1);

namespace Spieldose\Browse;

interface IBrowse
{
    public function browse(): \aportela\DatabaseBrowserWrapper\BrowserResults;
}
