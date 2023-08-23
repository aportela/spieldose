<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Artist extends \Spieldose\Entities\Entity
{
    public static function search(\aportela\DatabaseWrapper\DB $dbh, int $currentPage = 1, int $resultsPage = 32, $filter = array(), string $sortBy = "", string $sortOrder = ""): array
    {
        $params = array(
            new \aportela\DatabaseWrapper\Param\IntegerParam(":resultsPage", $resultsPage)
        );
        $filterConditions = array();
        if (isset($filter["name"]) && !empty($filter["name"])) {
            $filterConditions[] = " FIT.artist LIKE :name";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":name", "%" . $filter["name"] . "%");
        }
        $query = sprintf(
            "
                SELECT DISTINCT COALESCE(MB_CACHE_ARTIST.name, FIT.artist) AS name, MB_CACHE_ARTIST.image
                FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
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
