<?php

declare(strict_types=1);

namespace Spieldose;

class ArtistGenre
{
    public static function search(\aportela\DatabaseWrapper\DB $db, array $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $params = [];
        $filterConditions = [];
        if (isset($filter["name"]) && !empty($filter["name"])) {
            $words = explode(" ", trim((string) $filter["name"]));
            foreach ($words as $word) {
                $paramName = ":name_" . uniqid();
                $filterConditions[] = sprintf(" CACHE_MUSICBRAINZ_ARTIST_GENRE.genre LIKE %s", $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        }

        if (isset($filter["artistMbId"]) && !empty($filter["artistMbId"])) {
            $filterConditions[] = " CACHE_MUSICBRAINZ_ARTIST_GENRE.artist_mbid = :artist_mbid ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $filter["artistMbId"]);
        }

        $fieldDefinitions = [
            "name" => "CACHE_MUSICBRAINZ_ARTIST_GENRE.genre",
        ];
        $fieldCountDefinition = [
            "totalResults" => " COUNT(DISTINCT CACHE_MUSICBRAINZ_ARTIST_GENRE.genre)",
        ];
        $filter = new \aportela\DatabaseBrowserWrapper\Filter();

        $browser = new \aportela\DatabaseBrowserWrapper\Browser($db, $fieldDefinitions, $fieldCountDefinition, $pager, $sort, $filter);
        foreach ($params as $param) {
            $browser->addDBQueryParam($param);
        }

        $query = sprintf(
            "
                SELECT
                    DISTINCT %s
                FROM CACHE_MUSICBRAINZ_ARTIST_GENRE
                %s
                %s
                %s
            ",
            $browser->getQueryFields(),
            $filterConditions !== [] ? " WHERE " . implode(" AND ", $filterConditions) : null,
            $browser->getQuerySort(),
            $pager->getQueryLimit()
        );
        $queryCount = sprintf(
            "
                SELECT
                    %s
                FROM CACHE_MUSICBRAINZ_ARTIST_GENRE
                %s
            ",
            $browser->getQueryCountFields(),
            $filterConditions !== [] ? " WHERE " . implode(" AND ", $filterConditions) : null
        );
        return ($browser->launch($query, $queryCount));
    }
}
