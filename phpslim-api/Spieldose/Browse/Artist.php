<?php

declare(strict_types=1);

namespace Spieldose\Browse;

class Artist extends \Spieldose\Browse\Base
{
    public function browse(\aportela\DatabaseBrowserWrapper\Pager $pager): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $fieldDefinitions = [
            "name" => "TMP.name",
            "mbId" => "TMP.mbId",
            "image" => "TMP.image",
            "totalAlbums" => "TMP.totalAlbums",
            "totalTracks" => "TMP.totalTracks"
        ];
        $fieldCountDefinition = [
            "total" => "COUNT(TMP.name)"
        ];
        $sort = new \aportela\DatabaseBrowserWrapper\Sort([
            match ("") {
                default => new \aportela\DatabaseBrowserWrapper\SortItem("TMP.name", \aportela\DatabaseBrowserWrapper\Order::ASC, true),
            }
        ]);
        $filter = new \aportela\DatabaseBrowserWrapper\Filter([]);
        $afterBrowse = function (\aportela\DatabaseBrowserWrapper\BrowserResults $data) {
            array_map(
                function (object $item) {
                    if (property_exists($item, "age") && is_numeric($item->age)) {
                        $item->age = intval($item->age);
                    }
                    return ($item);
                },
                $data->items
            );
        };
        $browser = new \aportela\DatabaseBrowserWrapper\Browser(
            $this->dbh,
            $fieldDefinitions,
            $fieldCountDefinition,
            $pager,
            $sort,
            $filter,
            $afterBrowse
        );
        $query = $browser->buildQuery("
                SELECT %s FROM (
                    SELECT coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist) AS name, FILE_ID3_TAG.mb_artist_id AS mbId, CACHE_LASTFM_ARTIST.image, 0 AS totalAlbums, 0 AS totalTracks
                    FROM FILE_ID3_TAG
                    LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG.mb_artist_id
                    LEFT JOIN CACHE_LASTFM_ARTIST ON CACHE_LASTFM_ARTIST.name = coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist)
                    WHERE FILE_ID3_TAG.artist IS NOT NULL

                    UNION

                    SELECT coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.album_artist) AS name, FILE_ID3_TAG.mb_album_artist_id AS mbId, CACHE_LASTFM_ARTIST.image, 0 AS totalAlbums, 0 AS totalTracks
                    FROM FILE_ID3_TAG
                    LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG.mb_album_artist_id
                    LEFT JOIN CACHE_LASTFM_ARTIST ON CACHE_LASTFM_ARTIST.name = coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.album_artist)
                    WHERE FILE_ID3_TAG.album_artist IS NOT NULL

                ) TMP
                %s
                %s
        ");
        $countQuery = $browser->buildQueryCount("
                SELECT %s FROM (
                    SELECT coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist) AS name
                    FROM FILE_ID3_TAG
                    LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG.mb_artist_id
                    WHERE FILE_ID3_TAG.artist IS NOT NULL

                    UNION

                    SELECT coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.album_artist) AS name
                    FROM FILE_ID3_TAG
                    LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG.mb_album_artist_id
                    WHERE FILE_ID3_TAG.album_artist IS NOT NULL
                ) TMP
        ");
        return ($browser->launch($query, $countQuery, false));
    }
}
