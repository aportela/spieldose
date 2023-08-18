<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Track extends \Spieldose\Entities\Entity
{
    public static function search(\aportela\DatabaseWrapper\DB $dbh): array
    {
        $query = "
            SELECT FIT.id, FIT.title, FIT.artist, FIT.album, FIT.album_artist AS albumArtist, FIT.year, FIT.track_number as trackNumber
            FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
            ORDER BY RANDOM()
            LIMIT 32
        ";
        $params = array();
        $results = $dbh->query($query, $params);
        return ($results);
    }
}
