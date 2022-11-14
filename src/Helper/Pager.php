<?php

declare(strict_types=1);

namespace Spieldose\Helper;

class Pager
{
    public $currentPage;
    public $totalPages;
    public $resultsPage;

    public function __construct(int $currentPage = 1, int $resultsPage = 32)
    {
        $this->currentPage = $currentPage;
        $this->resultsPage = $resultsPage;
    }
}
