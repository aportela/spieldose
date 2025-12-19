<?php

declare(strict_types=1);

namespace Spieldose\Entities\PlayList;

class PlayListItem
{
    public \Spieldose\Entities\EntityImages $images;

    public function __construct()
    {
        $this->images = new \Spieldose\Entities\EntityImages(null, null, null);
    }
}
