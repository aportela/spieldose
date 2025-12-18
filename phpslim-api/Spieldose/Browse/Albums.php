<?php

declare(strict_types=1);

namespace Spieldose\Browse;

class Albums extends \Spieldose\Browse\Base
{
    public function browse(\aportela\DatabaseBrowserWrapper\Pager $pager, \aportela\DatabaseBrowserWrapper\Filter $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, bool $skipCount = false): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $this->pager = $pager;
        $this->filter = $filter;
        $this->sort = $sort;
        $this->fieldDefinitions = [
            "title" => "TMP.title",
            "mbId" => "TMP.mbId",
            "year" => "TMP.year",
        ];
        $this->fieldCountDefinition = [
            "total" => "COUNT(TMP.title)",
        ];
        $afterBrowse = function (\aportela\DatabaseBrowserWrapper\BrowserResults $browserResults): void {
            array_map(
                function (object $item): object {
                    if (property_exists($item, "totalTracks") && is_numeric($item->totalTracks)) {
                        $item->totalTracks = intval($item->totalTracks);
                    }

                    return ($item);
                },
                $browserResults->items
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
        if ($filter->hasParam("title") && is_string($filter->getParamValue("title"))) {
            $queryConditions[] = sprintf(" TMP.title LIKE %s ", ":title");
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":title", "%" . $filter->getParamValue("title") . "%");
        }

        $whereCondition = $queryConditions !== [] ? " WHERE " . implode(" AND ", $queryConditions) : "";
        $browser->addDBQueryParams($params);
        $query = $browser->buildQuery(
            sprintf(
                "
                    SELECT %%s FROM (
                        SELECT DISTINCT
                            coalesce(CACHE_MUSICBRAINZ_RELEASE.title, FILE_ID3_TAG.album) AS title, coalesce(CACHE_MUSICBRAINZ_RELEASE.year, FILE_ID3_TAG.year) AS year, FILE_ID3_TAG.release_mbid AS mbId
                        FROM FILE_ID3_TAG
                        LEFT JOIN CACHE_MUSICBRAINZ_RELEASE ON CACHE_MUSICBRAINZ_RELEASE.mbid = FILE_ID3_TAG.release_mbid
                        WHERE
                            coalesce(CACHE_MUSICBRAINZ_RELEASE.title, FILE_ID3_TAG.album) IS NOT NULL
                    ) TMP
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
                        SELECT DISTINCT
                            coalesce(CACHE_MUSICBRAINZ_RELEASE.title, FILE_ID3_TAG.album) AS title, coalesce(CACHE_MUSICBRAINZ_RELEASE.year, FILE_ID3_TAG.year) AS year, FILE_ID3_TAG.release_mbid AS mbId
                        FROM FILE_ID3_TAG
                        LEFT JOIN CACHE_MUSICBRAINZ_RELEASE ON CACHE_MUSICBRAINZ_RELEASE.mbid = FILE_ID3_TAG.release_mbid
                        WHERE
                            coalesce(CACHE_MUSICBRAINZ_RELEASE.title, FILE_ID3_TAG.album) IS NOT NULL
                    ) TMP
                    %s
                ",
                $whereCondition
            )
        );
        return ($browser->launch($query, $countQuery, $skipCount));
    }
}
