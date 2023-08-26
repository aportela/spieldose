<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Track extends \Spieldose\Entities\Entity
{
    public static function search(\aportela\DatabaseWrapper\DB $dbh, $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): array
    {
        $params = array();
        if ($pager->enabled) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":resultsPage", $pager->resultsPage);
        }
        $filterConditions = array();
        if (isset($filter["title"]) && !empty($filter["title"])) {
            $filterConditions[] = " FIT.title LIKE :title";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":title", "%" . $filter["title"] . "%");
        }
        if (isset($filter["artist"]) && !empty($filter["artist"])) {
            $filterConditions[] = " FIT.artist LIKE :artist";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":artist", "%" . $filter["artist"] . "%");
        }
        if (isset($filter["text"]) && !empty($filter["text"])) {
            $filterConditions[] = " (FIT.title LIKE :text OR FIT.artist LIKE :text OR FIT.album LIKE :text) ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":text", "%" . $filter["text"] . "%");
        }
        if (isset($filter["path"]) && !empty($filter["path"])) {
            $filterConditions[] = " EXISTS (SELECT DIRECTORY.id FROM FILE INNER JOIN DIRECTORY ON DIRECTORY.id = FILE.directory_id WHERE FILE.id = F.id AND DIRECTORY.id = :path)";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":path", $filter["path"]);
        }
        $query = sprintf(
            "
                SELECT FIT.id, FIT.title, FIT.artist, FIT.album, FIT.album_artist AS albumArtist, FIT.year, FIT.track_number as trackNumber, FIT.mb_album_id AS musicBrainzAlbumId
                FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.id = FIT.id
                %s
                ORDER BY RANDOM()
                %s
            ",
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
            $pager->enabled ? "LIMIT :resultsPage" : null
        );
        $results = $dbh->query($query, $params);
        return ($results);
    }
}
