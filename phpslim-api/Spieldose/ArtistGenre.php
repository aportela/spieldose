<?php

declare(strict_types=1);

namespace Spieldose;

class ArtistGenre
{

    public static function search(\aportela\DatabaseWrapper\DB $dbh, array $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $params = array();
        $filterConditions = array();
        if (isset($filter["name"]) && !empty($filter["name"])) {
            $words = explode(" ", trim($filter["name"]));
            foreach ($words as $word) {
                $paramName = ":name_" . uniqid();
                $filterConditions[] = sprintf(" MB_CACHE_ARTIST_GENRE.genre LIKE %s", $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        }
        if (isset($filter["artistMbId"]) && !empty($filter["artistMbId"])) {
            $filterConditions[] = " MB_CACHE_ARTIST_GENRE.artist_mbid = :artist_mbid ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $filter["artistMbId"]);
        }
        $fieldDefinitions = [
            "name" => "MB_CACHE_ARTIST_GENRE.genre"
        ];
        $fieldCountDefinition = [
            "totalResults" => " COUNT(DISTINCT MB_CACHE_ARTIST_GENRE.genre)"
        ];
        $filter = new \aportela\DatabaseBrowserWrapper\Filter();

        $browser = new \aportela\DatabaseBrowserWrapper\Browser($dbh, $fieldDefinitions, $fieldCountDefinition, $pager, $sort, $filter);
        foreach ($params as $param) {
            $browser->addDBQueryParam($param);
        }

        $query = sprintf(
            "
                SELECT
                    DISTINCT %s
                FROM MB_CACHE_ARTIST_GENRE
                %s
                %s
                %s
            ",
            $browser->getQueryFields(),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
            $browser->getQuerySort(),
            $pager->getQueryLimit()
        );
        $queryCount = sprintf(
            "
                SELECT
                    %s
                FROM MB_CACHE_ARTIST_GENRE
                %s
            ",
            $browser->getQueryCountFields(),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null
        );
        $data = $browser->launch($query, $queryCount);
        return ($data);
    }
}
