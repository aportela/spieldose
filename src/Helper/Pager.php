<?php

declare(strict_types=1);

namespace Spieldose\Helper;

class Pager
{
    public $currentPage;
    public $totalPages;
    public $resultsPage;

    public function __construct(int $currentPage, int $resultsPage)
    {
        $this->currentPage = $currentPage;
        $this->resultsPage = $resultsPage;
    }
}
