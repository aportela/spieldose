<?php

declare(strict_types=1);

namespace Spieldose\Helper;

class BrowserResults
{
    public $pager;
    public $sort;
    public $items;

    public function __construct(\Spieldose\Helper\Pager $pager, \Spieldose\Helper\Sort $sort, array $items = [])
    {
        $this->pager = $pager;
        $this->sort = $sort;
        $this->items = $items;
    }
}
