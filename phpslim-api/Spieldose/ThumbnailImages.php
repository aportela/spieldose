<?php

declare(strict_types=1);

namespace Spieldose;

class ThumbnailImages
{
    public string|null $small;
    public string|null $medium;
    public string|null $big;

    public function __construct()
    {
        $this->small = null;
        $this->medium = null;
        $this->big = null;
    }
}
