<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Track extends \Spieldose\Entities\Entity
{
    public static function search(\aportela\DatabaseWrapper\DB $dbh, int $currentPage = 1, int $resultsPage = 32, $filter = array(), string $sortBy = "", string $sortOrder = ""): array
    {
        $params = array(
            new \aportela\DatabaseWrapper\Param\IntegerParam(":resultsPage", $resultsPage)
        );
        $filterConditions = array();
        if (isset($filter["title"]) && !empty($filter["title"])) {
            $filterConditions[] = " FIT.title LIKE :title";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":title", "%" . $filter["title"] . "%");
        }
        if (isset($filter["artist"]) && !empty($filter["artist"])) {
            $filterConditions[] = " FIT.artist LIKE :artist";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":artist", "%" . $filter["artist"] . "%");
        }
        $query = sprintf(
            "
                SELECT FIT.id, FIT.title, FIT.artist, FIT.album, FIT.album_artist AS albumArtist, FIT.year, FIT.track_number as trackNumber, FIT.mb_album_id AS musicBrainzAlbumId
                FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                %s
                ORDER BY RANDOM()
                LIMIT :resultsPage
            ",
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null
        );
        $results = $dbh->query($query, $params);
        return ($results);
    }
}
