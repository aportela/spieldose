<?php

declare(strict_types=1);

namespace Spieldose\Entities\PlayList;

final class PlayListFlags
{
    public bool $isMine;
    public bool $opened;
    public bool $actived;
    public bool $published;
    public bool $shared;
    public bool $isFavorites;

    public function __construct(bool $isMine, bool $opened, bool $actived, bool $published, bool $shared, bool $isFavorites)
    {
        $this->isMine = $isMine;
        $this->opened = $opened;
        $this->actived = $actived;
        $this->published = $published;
        $this->shared = $shared;
        $this->isFavorites = $isFavorites;
    }
}
