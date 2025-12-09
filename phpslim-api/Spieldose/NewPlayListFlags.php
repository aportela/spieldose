<?php

declare(strict_types=1);

namespace Spieldose;

class NewPlayListFlags
{
    public bool $isMine;
    public bool $opened;
    public bool $published;
    public bool $shared;
    public bool $isFavorites;

    public function __construct(bool $isMine, bool $opened, bool $published, bool $shared, bool $isFavorites)
    {
        $this->isMine = $isMine;
        $this->opened = $opened;
        $this->published = $published;
        $this->shared = $shared;
        $this->isFavorites = $isFavorites;
    }
}
