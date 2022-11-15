<?php

declare(strict_types=1);

namespace Spieldose\Helper;

class Pager
{
    public $currentPage;
    public $resultsPage;
    public $totalPages;
    public $totalResults;

    public function __construct(int $currentPage = 1, int $resultsPage = 32)
    {
        $this->currentPage = $currentPage;
        $this->resultsPage = $resultsPage;
    }

    public function setTotalResults(int $totalResults) {
        $this->totalResults = $totalResults;
        if ($this->resultsPage > 0) {
            $this->totalPages = ceil($this->totalResults / $this->resultsPage);
        } else {
            $this->totalPages = 0;
        }
    }

    public function getSQLQueryLimitFrom() {
        return(($this->currentPage - 1) * $this->resultsPage);
    }
}
