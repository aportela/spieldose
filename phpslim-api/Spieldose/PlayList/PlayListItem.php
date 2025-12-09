<?php

declare(strict_types=1);

namespace Spieldose\PlayList;

class PlayListItem
{
    public \Spieldose\ThumbnailImages $images;

    public function __construct()
    {
        $this->images = new \Spieldose\ThumbnailImages();
    }
}
