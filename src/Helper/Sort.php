<?php

declare(strict_types=1);

namespace Spieldose\Helper;

class Sort
{
    const ASCENDING_ORDER = "ASC";
    const DESCENDING_ORDER = "DESC";

    public $field;
    public $order;

    public function __construct(int $field, int $order)
    {
        $this->field = $field;
        $this->order = $order;
    }
}
