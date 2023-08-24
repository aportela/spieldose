<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Album extends \Spieldose\Entities\Entity
{
    public $mbId;
    public string $title;
    public object $artist;
    public ?string $image;
    public ?int $year;

    public static function search(\aportela\DatabaseWrapper\DB $dbh, array $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $params = array();
        $filterConditions = array();
        if (isset($filter["title"]) && !empty($filter["title"])) {
            $filterConditions[] = " COALESCE(MB_CACHE_RELEASE.title, FIT.album) LIKE :title";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":title", "%" . $filter["title"] . "%");
        } else {
            $filterConditions[] = " COALESCE(MB_CACHE_RELEASE.title, FIT.album) IS NOT NULL ";
        }
        $fieldDefinitions = [
            "albumMbId" => "FIT.mb_album_id",
            "title" => "COALESCE(MB_CACHE_RELEASE.title, FIT.album)",
            "artistName" => "COALESCE(MB_CACHE_RELEASE.artist_name, MB_CACHE_ARTIST.name, FIT.album_artist, FIT.artist)",
            "artistMbId" => "COALESCE(MB_CACHE_RELEASE.artist_mbid, NULL)",
            "image" => " NULL ",
            "year" => "COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))",
        ];

        $fieldCountDefinition = [
            "totalResults" => " COUNT(DISTINCT COALESCE(MB_CACHE_RELEASE.title, FIT.album))"
        ];

        $filter = new \aportela\DatabaseBrowserWrapper\Filter();

        $afterBrowseFunction = function ($data) {
            $data->items = array_map(
                function ($result) {
                    $result->artist = new \stdClass();
                    $result->artist->mbId = $result->artistMbId;
                    $result->artist->name = $result->artistName;
                    if (!empty($result->albumMbId)) {
                        $coverArtURL = sprintf("https://coverartarchive.org/release/%s/front-250.jpg", $result->albumMbId);
                        $result->image = sprintf("api/2/thumbnail_remote/normal/?url=%s", urlencode($coverArtURL));
                    }
                    unset($result->albumMbId);
                    unset($result->artistMbId);
                    unset($result->artistName);
                    return ($result);
                },
                $data->items
            );
        };

        $browser = new \aportela\DatabaseBrowserWrapper\Browser($dbh, $fieldDefinitions, $fieldCountDefinition, $pager, $sort, $filter, $afterBrowseFunction);
        foreach ($params as $param) {
            $browser->addDBQueryParam($param);
        }
        $query = sprintf(
            "
                SELECT DISTINCT %s
                FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
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
                FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                %s
            ",
            $browser->getQueryCountFields(),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null
        );
        $data = $browser->launch($query, $queryCount);
        return ($data);
    }
}
