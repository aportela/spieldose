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
            "image" => "CACHE_LASTFM_ARTIST.image",
            "totalTracks" => "COALESCE(TOTAL_TRACKS.total, 0)"
        ];
        $this->fieldCountDefinition = [
            "total" => "COUNT(TMP.name)"
        ];
        $afterBrowse = function (\aportela\DatabaseBrowserWrapper\BrowserResults $data) {
            array_map(
                function (object $item): object {
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
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":various_artists_mbid", "89ad4ac3-39f7-470e-963a-56509c546377")
        ];
        if ($filter->hasParam("name") && is_string($filter->getParamValue("name"))) {
            $queryConditions[] = sprintf(" TMP.name LIKE %s ", ":name");
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":name",  "%" . $filter->getParamValue("name") . "%");
        }
        $whereCondition = $queryConditions !== [] ? " WHERE " .  implode(" AND ", $queryConditions) : "";
        $browser->addDBQueryParams($params);
        // TODO: add CACHE_MUSICBRAINZ_RECORDING_ARTIST
        $query = $browser->buildQuery(
            sprintf(
                "
                    SELECT %%s FROM (
                        SELECT
                            coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist) AS name, FILE_ID3_TAG_MUSICBRAINZ_ARTIST.artist_mbid AS mbId
                        FROM FILE_ID3_TAG
                        LEFT JOIN FILE_ID3_TAG_MUSICBRAINZ_ARTIST ON FILE_ID3_TAG_MUSICBRAINZ_ARTIST.file_id = FILE_ID3_TAG.file_id
                        LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG_MUSICBRAINZ_ARTIST.artist_mbid
                        WHERE
                            coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist) IS NOT NULL

                        UNION

                        SELECT
                            coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.album_artist) AS name, FILE_ID3_TAG_MUSICBRAINZ_RELEASE_ARTIST.artist_mbid AS mbId
                        FROM FILE_ID3_TAG
                        LEFT JOIN FILE_ID3_TAG_MUSICBRAINZ_RELEASE_ARTIST ON FILE_ID3_TAG_MUSICBRAINZ_RELEASE_ARTIST.file_id = FILE_ID3_TAG.file_id
                        LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG_MUSICBRAINZ_RELEASE_ARTIST.artist_mbid
                        WHERE
                            FILE_ID3_TAG_MUSICBRAINZ_RELEASE_ARTIST.artist_mbid <> :various_artists_mbid
                        AND
                            coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.album_artist) IS NOT NULL

                        UNION

                        SELECT
                            CACHE_MUSICBRAINZ_ARTIST.name AS name, CACHE_MUSICBRAINZ_ARTIST.mbid AS mbId
                        FROM FILE_ID3_TAG
                        INNER JOIN CACHE_MUSICBRAINZ_TRACK ON CACHE_MUSICBRAINZ_TRACK.mbid = FILE_ID3_TAG.release_track_mbid
                        INNER JOIN CACHE_MUSICBRAINZ_RECORDING ON CACHE_MUSICBRAINZ_RECORDING.mbid = CACHE_MUSICBRAINZ_TRACK.recording_mbid
                        INNER JOIN CACHE_MUSICBRAINZ_RECORDING_ARTIST ON CACHE_MUSICBRAINZ_RECORDING_ARTIST.recording_mbid = CACHE_MUSICBRAINZ_RECORDING.mbid
                        INNER JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = CACHE_MUSICBRAINZ_RECORDING_ARTIST.artist_mbid
                    ) TMP
                    LEFT JOIN CACHE_LASTFM_ARTIST ON CACHE_LASTFM_ARTIST.name = TMP.name
                    LEFT JOIN (
                        SELECT coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist) AS name, COUNT(*) AS total
                        FROM FILE_ID3_TAG
                        LEFT JOIN FILE_ID3_TAG_MUSICBRAINZ_ARTIST ON FILE_ID3_TAG_MUSICBRAINZ_ARTIST.file_id = FILE_ID3_TAG.file_id
                        LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG_MUSICBRAINZ_ARTIST.artist_mbid
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
                        SELECT
                            coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist) AS name, FILE_ID3_TAG_MUSICBRAINZ_ARTIST.artist_mbid AS mbId
                        FROM FILE_ID3_TAG
                        LEFT JOIN FILE_ID3_TAG_MUSICBRAINZ_ARTIST ON FILE_ID3_TAG_MUSICBRAINZ_ARTIST.file_id = FILE_ID3_TAG.file_id
                        LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG_MUSICBRAINZ_ARTIST.artist_mbid
                        WHERE
                            coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.artist) IS NOT NULL

                        UNION

                        SELECT
                            coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.album_artist) AS name, FILE_ID3_TAG_MUSICBRAINZ_RELEASE_ARTIST.artist_mbid AS mbId
                        FROM FILE_ID3_TAG
                        LEFT JOIN FILE_ID3_TAG_MUSICBRAINZ_RELEASE_ARTIST ON FILE_ID3_TAG_MUSICBRAINZ_RELEASE_ARTIST.file_id = FILE_ID3_TAG.file_id
                        LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG_MUSICBRAINZ_RELEASE_ARTIST.artist_mbid
                        WHERE
                            FILE_ID3_TAG_MUSICBRAINZ_RELEASE_ARTIST.artist_mbid <> :various_artists_mbid
                        AND
                            coalesce(CACHE_MUSICBRAINZ_ARTIST.name, FILE_ID3_TAG.album_artist) IS NOT NULL

                        UNION

                        SELECT
                            CACHE_MUSICBRAINZ_ARTIST.name AS name, CACHE_MUSICBRAINZ_ARTIST.mbid AS mbId
                        FROM FILE_ID3_TAG
                        INNER JOIN CACHE_MUSICBRAINZ_TRACK ON CACHE_MUSICBRAINZ_TRACK.mbid = FILE_ID3_TAG.release_track_mbid
                        INNER JOIN CACHE_MUSICBRAINZ_RECORDING ON CACHE_MUSICBRAINZ_RECORDING.mbid = CACHE_MUSICBRAINZ_TRACK.recording_mbid
                        INNER JOIN CACHE_MUSICBRAINZ_RECORDING_ARTIST ON CACHE_MUSICBRAINZ_RECORDING_ARTIST.recording_mbid = CACHE_MUSICBRAINZ_RECORDING.mbid
                        INNER JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = CACHE_MUSICBRAINZ_RECORDING_ARTIST.artist_mbid
                    ) TMP
                    %s
                ",
                $whereCondition
            )
        );
        return ($browser->launch($query, $countQuery, $skipCount));
    }
}
