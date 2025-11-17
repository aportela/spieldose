<?php

declare(strict_types=1);

namespace Spieldose\Browse;

class Artist extends \Spieldose\Browse\Base
{
    public function browse(\aportela\DatabaseBrowserWrapper\Pager $pager, \aportela\DatabaseBrowserWrapper\Filter $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, bool $skipCount = false): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $this->pager = $pager;
        $this->filter = $filter;
        $this->sort = $sort;
        $this->fieldDefinitions = [
            "name" => "TMP.name",
            "mbId" => "TMP.mbId",
            "image" => "TMP.image",
            "totalTracks" => "COALESCE(TOTAL_TRACKS.total, 0)"
        ];
        $this->fieldCountDefinition = [
            "total" => "COUNT(TMP.name)"
        ];
        $afterBrowse = function (\aportela\DatabaseBrowserWrapper\BrowserResults $data) {
            array_map(
                function (object $item) {
                    if (property_exists($item, "totalTracks") && is_numeric($item->totalTracks)) {
                        $item->totalTracks = intval($item->totalTracks);
                    }
                    return ($item);
                },
                $data->items
            );
        };
        $browser = new \aportela\DatabaseBrowserWrapper\Browser(
            $this->dbh,
            $this->fieldDefinitions,
            $this->fieldCountDefinition,
            $this->pager,
            $this->sort,
            $this->filter,
            $afterBrowse
        );
        $queryConditions = [];
        $params = [];
        if ($filter->hasParam("name") && is_string($filter->getParamValue("name"))) {
            $queryConditions[] = sprintf(" TMP.name LIKE %s ", ":name");
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":name",  "%" . $filter->getParamValue("name") . "%");
        }
        $whereCondition = $queryConditions !== [] ? " WHERE " .  implode(" AND ", $queryConditions) : "";
        $browser->addDBQueryParams($params);
        $query = $browser->buildQuery(
            sprintf(
                "
                    SELECT %%s FROM (
                        SELECT DISTINCT coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist) AS name, FILE_ID3_TAG.mb_artist_id AS mbId, CACHE_LASTFM_ARTIST.image, 0 AS totalTracks
                        FROM FILE_ID3_TAG
                        LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG.mb_artist_id
                        LEFT JOIN CACHE_LASTFM_ARTIST ON CACHE_LASTFM_ARTIST.name = coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist)
                        WHERE coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist) IS NOT NULL
                    ) TMP
                    LEFT JOIN (
                        SELECT coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist) AS name, COUNT(*) AS total
                        FROM FILE_ID3_TAG
                        LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG.mb_artist_id
                        GROUP BY 1
                        HAVING coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist) NOT NULL
                    ) TOTAL_TRACKS ON TOTAL_TRACKS.name = TMP.name
                    %s
                    %%s
                    %%s
                ",
                $whereCondition
            )
        );
        $countQuery = $browser->buildQueryCount(
            sprintf(
                "
                    SELECT %%s FROM (
                        SELECT DISTINCT coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist) AS name
                        FROM FILE_ID3_TAG
                        LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG.mb_artist_id
                        WHERE FILE_ID3_TAG.artist IS NOT NULL
                    ) TMP
                    %s
                ",
                $whereCondition
            )
        );
        return ($browser->launch($query, $countQuery, $skipCount));
    }
}
