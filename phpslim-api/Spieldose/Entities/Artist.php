<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Artist extends \Spieldose\Entities\Entity
{
    public $name = null;
    public $image = null;
    public $relations = null;
    public $bio = null;
    public $popularAlbum = null;
    public $latestAlbum = null;
    public $topTracks = [];
    public $topAlbums = [];
    public $appearsOnAlbums = [];
    public $similar = [];
    public $genres = [];

    /*
    public function __construct()
    {
        $this->relations = [];
        $this->topTracks = [];
        $this->topAlbums = [];
        $this->appearsOnAlbums = [];
        $this->similar = [];
        $this->genres = [];
    }
    */

    public static function search(\aportela\DatabaseWrapper\DB $dbh, array $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        // TODO: ignore ids like "/"
        $params = array();
        $filterConditions = array();
        if (isset($filter["name"]) && !empty($filter["name"])) {
            $words = explode(" ", trim($filter["name"]));
            foreach ($words as $word) {
                $paramName = ":name_" . uniqid();
                $filterConditions[] = sprintf(" artist_name LIKE %s", $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        } else {
            $filterConditions[] = " artist_name IS NOT NULL ";
        }
        if (isset($filter["genre"]) && !empty($filter["genre"])) {
            $filterConditions[] = " EXISTS (SELECT CACHE_ARTIST_MUSICBRAINZ_GENRE.genre FROM CACHE_ARTIST_MUSICBRAINZ_GENRE WHERE CACHE_ARTIST_MUSICBRAINZ_GENRE.artist_mbid = TMP_ARTISTS.mb_artist_id AND CACHE_ARTIST_MUSICBRAINZ_GENRE.genre = :genre) ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":genre", $filter["genre"]);
        }
        $fieldDefinitions = [
            "mbId" => "artist_mbid",
            "name" => "artist_name",
            "image" => "COALESCE(CACHE_ARTIST_LASTFM.image, CACHE_ARTIST_MUSICBRAINZ.image)",
            "totalTracks" => " COALESCE(TOTAL_TRACKS_BY_ARTIST_MBID.total, TOTAL_TRACKS_BY_ARTIST_NAME.total, 0) "
        ];
        $fieldCountDefinition = [
            "totalResults" => " COUNT(artist_name) "
        ];
        $filter = new \aportela\DatabaseBrowserWrapper\Filter();

        $afterBrowseFunction = function ($data) {
            $data->items = array_map(
                function ($result) {
                    if (!empty($result->image)) {
                        $result->image = sprintf(\Spieldose\API::REMOTE_ARTIST_URL_SMALL_THUMBNAIL, urlencode($result->image));
                    }
                    // create a unique hash for this element, this is done because artist name && artist musicbrainz are not both mandatory and can not be used as key on vue v-for
                    $result->hash = md5($result->mbId . $result->name);
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
                SELECT %s
                FROM (
                    SELECT DISTINCT COALESCE(CACHE_ARTIST_MUSICBRAINZ.name, FIT.artist) AS artist_name, FIT.mb_artist_id AS artist_mbid
                    FROM FILE_ID3_TAG FIT
                    LEFT JOIN CACHE_ARTIST_MUSICBRAINZ ON CACHE_ARTIST_MUSICBRAINZ.mbid = FIT.mb_artist_id
                    WHERE (FIT.artist IS NOT NULL OR FIT.mb_artist_id IS NOT NULL)
                    %s
                    %s
                    %s
                ) TMP_ARTISTS
                LEFT JOIN CACHE_ARTIST_MUSICBRAINZ ON CACHE_ARTIST_MUSICBRAINZ.mbid = TMP_ARTISTS.artist_mbid
                LEFT JOIN CACHE_ARTIST_LASTFM ON ((TMP_ARTISTS.artist_mbid IS NOT NULL AND CACHE_ARTIST_LASTFM.mbid = TMP_ARTISTS.artist_mbid) OR (CACHE_ARTIST_LASTFM.name = TMP_ARTISTS.artist_name))
                LEFT JOIN (
                    SELECT FILE_ID3_TAG.mb_artist_id AS artistMBId, COUNT(*) AS total
                    FROM FILE_ID3_TAG
                    GROUP BY FILE_ID3_TAG.mb_artist_id
                    HAVING FILE_ID3_TAG.mb_artist_id NOT NULL
                ) AS TOTAL_TRACKS_BY_ARTIST_MBID ON TOTAL_TRACKS_BY_ARTIST_MBID.artistMBId = TMP_ARTISTS.artist_mbid
                LEFT JOIN (
                    SELECT FILE_ID3_TAG.artist AS artistName, COUNT(*) AS total
                    FROM FILE_ID3_TAG
                    GROUP BY FILE_ID3_TAG.artist
                    HAVING FILE_ID3_TAG.artist NOT NULL
                ) AS TOTAL_TRACKS_BY_ARTIST_NAME ON TOTAL_TRACKS_BY_ARTIST_NAME.artistName = TMP_ARTISTS.artist_name
                %s
                %s
            ",
            $browser->getQueryFields(),
            count($filterConditions) > 0 ? " AND " . implode(" AND ", $filterConditions) : null,
            $browser->isSortedBy("name") ? " ORDER BY 1 " . $browser->getSortOrder("name") : null,
            !$browser->isSortedBy("totalTracks") ? $pager->getQueryLimit() : null,
            !$browser->isSortedBy("name") ? $browser->getQuerySort() : null,
            $browser->isSortedBy("totalTracks") ? $pager->getQueryLimit() : null,
        );
        $queryCount = sprintf(
            "
                SELECT
                %s
                FROM (
                    SELECT DISTINCT COALESCE(CACHE_ARTIST_MUSICBRAINZ.name, FIT.artist) AS artist_name, FIT.mb_artist_id AS artist_mbid
                    FROM FILE_ID3_TAG FIT
                    LEFT JOIN CACHE_ARTIST_MUSICBRAINZ ON CACHE_ARTIST_MUSICBRAINZ.mbid = FIT.mb_artist_id
                    WHERE (FIT.artist IS NOT NULL OR FIT.mb_artist_id IS NOT NULL)
                    %s
                ) TMP_ARTISTS
            ",
            $browser->getQueryCountFields(),
            count($filterConditions) > 0 ? " AND " . implode(" AND ", $filterConditions) : null
        );
        $data = $browser->launch($query, $queryCount);
        return ($data);
    }

    private function getMBIdFromName(string $name)
    {
        $query = " SELECT mbid FROM CACHE_ARTIST_MUSICBRAINZ WHERE name = :name ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":name", $name)
        );
        $results = $this->dbh->query($query, $params);
        if (count($results) == 1) {
            return ($results[0]->mbid);
        } else {
            return (null);
        }
    }

    private function getTopTracks(\aportela\DatabaseWrapper\DB $dbh, array $filter): array
    {
        $trackFields = [
            "id " => "FIT.id",
            "mbId" => "FIT.mb_release_track_id",
            "title" => "FIT.title",
            "artistMBId" => "FIT.mb_artist_id",
            "artistName" => "COALESCE(CACHE_ARTIST_MUSICBRAINZ.name, FIT.artist)",
            "releaseMBId" => "FIT.mb_album_id",
            "releaseTitle" => "COALESCE(MB_CACHE_RELEASE.title, FIT.album)",
            "albumArtistMBId" => "COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id)",
            "albumArtistName" => "COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist)",
            "year" => "COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))",
            "trackNumber" => "FIT.track_number",
            "coverPathId" => "D.id",
            "playCount" => "COALESCE(TMP_COUNT.total, 0)",
            "favorited" => "FF.favorited"
        ];

        $fields = [];
        foreach ($trackFields as $key => $value) {
            $fields[] = $value . " AS " . $key;
        }

        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId())
        ];

        $filterConditions = [];

        if (isset($filter["mbId"]) && !empty($filter["mbId"])) {
            $filterConditions[] = " FIT.mb_artist_id = :mbid ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $filter["mbId"]);
        } elseif (isset($filter["name"]) && !empty($filter["name"])) {
            $filterConditions[] = " COALESCE(CACHE_ARTIST_MUSICBRAINZ.name, FIT.artist) = :name ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":name", $filter["name"]);
        }

        $query = sprintf(
            "
                SELECT
                    %s
                FROM FILE_ID3_TAG FIT
                INNER JOIN FILE F ON F.id = FIT.id
                INNER JOIN (
                    SELECT file_id, COUNT(*) AS total
                    FROM FILE_PLAYCOUNT_STATS
                    WHERE user_id = :user_id
                    GROUP BY file_id
                    HAVING COUNT(*) > 0
                ) TMP_COUNT ON TMP_COUNT.file_id = FIT.id
                LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                LEFT JOIN CACHE_ARTIST_MUSICBRAINZ ON CACHE_ARTIST_MUSICBRAINZ.mbid = FIT.mb_artist_id
                LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                LEFT JOIN FILE_FAVORITE FF ON FF.file_id = FIT.id AND FF.user_id = :user_id
                %s
                ORDER BY COALESCE(TMP_COUNT.total, 0) DESC
                LIMIT 10
            ",
            implode(", ", $fields),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null
        );

        $topTracks = [];
        foreach ($dbh->query($query, $params) as $result) {
            $track = (array) new \Spieldose\Entities\Track(
                $result->id,
                $result->mbId,
                $result->title,
                $result->artistMBId,
                $result->artistName,
                $result->releaseMBId,
                $result->releaseTitle,
                $result->albumArtistMBId,
                $result->albumArtistName,
                $result->year,
                $result->trackNumber,
                $result->coverPathId,
                $result->favorited
            );
            $track["playCount"] = $result->playCount;
            $topTracks[] = $track;
        }
        return ($topTracks);
    }

    private function getSimilarArtists(\aportela\DatabaseWrapper\DB $dbh, array $filter, int $limitCount = 32): array
    {
        $artistFields = [
            "mbId" => "TMP_ARTISTS.mb_artist_id",
            "name" => "TMP_ARTISTS.artist",
            "image" => "COALESCE(CACHE_ARTIST_LASTFM.image, CACHE_ARTIST_MUSICBRAINZ.image)",
            "totalTracks" => " COALESCE(TOTAL_TRACKS_BY_ARTIST_MBID.total, TOTAL_TRACKS_BY_ARTIST_NAME.total, 0) "
        ];

        $fields = [];
        foreach ($artistFields as $key => $value) {
            $fields[] = $value . " AS " . $key;
        }

        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":md5_hash", md5($filter["mbId"] . $filter["name"]))
        ];

        $filterConditions = [
            // direct relation -> artist has this relation
            "
                EXISTS (
                    SELECT CALS.name
                    FROM CACHE_ARTIST_LASTFM_SIMILAR CALS
                    WHERE CALS.artist_hash = :md5_hash
                    AND CALS.name = TMP_ARTISTS.artist
                )
            "

        ];

        $query = sprintf(
            "
                SELECT DISTINCT %s
                FROM (
                    SELECT DISTINCT COALESCE(CACHE_ARTIST_MUSICBRAINZ.name, FIT.artist) AS artist, FIT.mb_artist_id
                    FROM FILE_ID3_TAG FIT
                    LEFT JOIN CACHE_ARTIST_MUSICBRAINZ ON CACHE_ARTIST_MUSICBRAINZ.mbid = FIT.mb_artist_id
                    WHERE FIT.artist IS NOT NULL OR FIT.mb_artist_id IS NOT NULL
                ) TMP_ARTISTS
                LEFT JOIN CACHE_ARTIST_MUSICBRAINZ ON CACHE_ARTIST_MUSICBRAINZ.mbid = TMP_ARTISTS.mb_artist_id
                LEFT JOIN CACHE_ARTIST_LASTFM ON ((TMP_ARTISTS.mb_artist_id IS NOT NULL AND CACHE_ARTIST_LASTFM.mbid = TMP_ARTISTS.mb_artist_id) OR (CACHE_ARTIST_LASTFM.name = TMP_ARTISTS.artist))
                LEFT JOIN (
                    SELECT FILE_ID3_TAG.mb_artist_id AS artistMBId, COUNT(*) AS total
                    FROM FILE_ID3_TAG
                    GROUP BY FILE_ID3_TAG.mb_artist_id
                    HAVING FILE_ID3_TAG.mb_artist_id NOT NULL
                ) AS TOTAL_TRACKS_BY_ARTIST_MBID ON TOTAL_TRACKS_BY_ARTIST_MBID.artistMBId = TMP_ARTISTS.mb_artist_id
                LEFT JOIN (
                    SELECT FILE_ID3_TAG.artist AS artistName, COUNT(*) AS total
                    FROM FILE_ID3_TAG
                    GROUP BY FILE_ID3_TAG.artist
                    HAVING FILE_ID3_TAG.artist NOT NULL
                ) AS TOTAL_TRACKS_BY_ARTIST_NAME ON TOTAL_TRACKS_BY_ARTIST_NAME.artistName = TMP_ARTISTS.artist
                %s
                ORDER BY RANDOM()
                LIMIT %d
            ",
            implode(", ", $fields),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
            $limitCount
        );

        $similarArtists = $dbh->query($query, $params);

        // no similar by last.fm, try by musicbrainz genres
        if (count($similarArtists) < 1 && !empty($filter["mbId"])) {
            $params = [
                new \aportela\DatabaseWrapper\Param\StringParam("artist_mbid", $filter["mbId"])
            ];
            $filterConditions = [
                "
                    EXISTS (
                        SELECT CAMG1.genre
                        FROM CACHE_ARTIST_MUSICBRAINZ_GENRE CAMG1
                        INNER JOIN CACHE_ARTIST_MUSICBRAINZ_GENRE CAMG2 ON CAMG2.genre = CAMG1.genre
                        WHERE CAMG1.artist_mbid = TMP_ARTISTS.mb_artist_id
                        AND CAMG2.artist_mbid = :artist_mbid
                    )
                "
            ];

            $query = sprintf(
                "
                    SELECT DISTINCT %s
                    FROM (
                        SELECT DISTINCT COALESCE(CACHE_ARTIST_MUSICBRAINZ.name, FIT.artist) AS artist, FIT.mb_artist_id
                        FROM FILE_ID3_TAG FIT
                        LEFT JOIN CACHE_ARTIST_MUSICBRAINZ ON CACHE_ARTIST_MUSICBRAINZ.mbid = FIT.mb_artist_id
                        WHERE FIT.artist IS NOT NULL OR FIT.mb_artist_id IS NOT NULL
                    ) TMP_ARTISTS
                    LEFT JOIN CACHE_ARTIST_MUSICBRAINZ ON CACHE_ARTIST_MUSICBRAINZ.mbid = TMP_ARTISTS.mb_artist_id
                    LEFT JOIN CACHE_ARTIST_LASTFM ON ((TMP_ARTISTS.mb_artist_id IS NOT NULL AND CACHE_ARTIST_LASTFM.mbid = TMP_ARTISTS.mb_artist_id) OR (CACHE_ARTIST_LASTFM.name = TMP_ARTISTS.artist))
                    LEFT JOIN (
                        SELECT FILE_ID3_TAG.mb_artist_id AS artistMBId, COUNT(*) AS total
                        FROM FILE_ID3_TAG
                        GROUP BY FILE_ID3_TAG.mb_artist_id
                        HAVING FILE_ID3_TAG.mb_artist_id NOT NULL
                    ) AS TOTAL_TRACKS_BY_ARTIST_MBID ON TOTAL_TRACKS_BY_ARTIST_MBID.artistMBId = TMP_ARTISTS.mb_artist_id
                    LEFT JOIN (
                        SELECT FILE_ID3_TAG.artist AS artistName, COUNT(*) AS total
                        FROM FILE_ID3_TAG
                        GROUP BY FILE_ID3_TAG.artist
                        HAVING FILE_ID3_TAG.artist NOT NULL
                    ) AS TOTAL_TRACKS_BY_ARTIST_NAME ON TOTAL_TRACKS_BY_ARTIST_NAME.artistName = TMP_ARTISTS.artist
                    %s
                    ORDER BY RANDOM()
                    LIMIT %d
                ",
                implode(", ", $fields),
                count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
                $limitCount
            );

            $similarArtists = $dbh->query($query, $params);
        }

        foreach ($similarArtists as $artist) {
            if (!empty($artist->image)) {
                $artist->image = sprintf(\Spieldose\API::REMOTE_ARTIST_URL_SMALL_THUMBNAIL, urlencode($artist->image));
            }
            // create a unique hash for this element, this is done because artist name && artist musicbrainz are not both mandatory and can not be used as key on vue v-for
            $artist->hash = md5($artist->mbId . $artist->name);
        }
        return ($similarArtists);
    }

    public function get(bool $useLocalCovers = true): void
    {
        if (empty($this->mbId) && !empty($this->name)) {
            $this->mbId = $this->getMBIdFromName(($this->name));
        }
        // TODO: get if no mbId
        if (!empty($this->mbId)) {
            $query = "
                SELECT
                    CACHE_ARTIST_MUSICBRAINZ.name,
                    COALESCE(CACHE_ARTIST_LASTFM.image, CACHE_ARTIST_MUSICBRAINZ.image) AS image,
                    COALESCE(CACHE_ARTIST_WIKIPEDIA.intro, CACHE_ARTIST_LASTFM.bio_summary) AS bio_summary,
                    COALESCE(CACHE_ARTIST_WIKIPEDIA.page, CACHE_ARTIST_LASTFM.bio_content) AS bio_content,
                    IIF(CACHE_ARTIST_WIKIPEDIA.page IS NOT NULL, 'wikipedia', IIF(CACHE_ARTIST_LASTFM.bio_content IS NOT NULL, 'lastfm', NULL)) AS bio_source
                FROM CACHE_ARTIST_MUSICBRAINZ
                LEFT JOIN CACHE_ARTIST_WIKIPEDIA ON CACHE_ARTIST_WIKIPEDIA.mbid = CACHE_ARTIST_MUSICBRAINZ.mbid
                LEFT JOIN CACHE_ARTIST_LASTFM ON CACHE_ARTIST_LASTFM.name = CACHE_ARTIST_MUSICBRAINZ.name
                WHERE CACHE_ARTIST_MUSICBRAINZ.mbid = :mbid
            ";
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
            );
            $results = $this->dbh->query($query, $params);
            if (count($results) == 1) {
                $this->name = $results[0]->name;
                $this->image = $results[0]->image;
                if (!empty($this->image)) {
                    // TODO: use API URL
                    $this->image = sprintf("api/2/thumbnail/normal/remote/artist/?url=%s", urlencode($this->image));
                }
                $this->bio = (object) [
                    "source" => $results[0]->bio_source,
                    "summary" => $results[0]->bio_summary,
                    "content" => $results[0]->bio_content
                ];
                $query = " SELECT relation_type_id, url FROM CACHE_ARTIST_MUSICBRAINZ_URL_RELATIONSHIP WHERE artist_mbid = :mbid ORDER BY name COLLATE NOCASE";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                $results = $this->dbh->query($query, $params);
                if (count($results) > 0) {
                    foreach ($results as $result) {
                        $this->relations[] = (object) ["type-id" => $result->relation_type_id, "url" => $result->url];
                    }
                } else {
                    $this->relations = [];
                }
                $query = " SELECT DISTINCT genre FROM CACHE_ARTIST_MUSICBRAINZ_GENRE WHERE artist_mbid = :mbid ORDER BY genre";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                $results = $this->dbh->query($query, $params);
                if (count($results) > 0) {
                    foreach ($results as $result) {
                        $this->genres[] = $result->genre;
                    }
                } else {
                    $this->genres = [];
                }
                $query = "
                    SELECT DISTINCT FIT.album, FIT.mb_album_id AS mbId, FIT.year, D.id AS coverPathId
                    FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                    LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                    WHERE FIT.mb_artist_id = :mbid AND FIT.album IS NOT NULL
                    ORDER BY RANDOM()
                    LIMIT 1
                ";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                $results = $this->dbh->query($query, $params);
                $this->popularAlbum = (object) [
                    'title' => null,
                    'year' => null,
                    'image' => null
                ];
                if (count($results) == 1) {
                    $this->popularAlbum->title = $results[0]->album;
                    $this->popularAlbum->year = $results[0]->year;
                    if ($useLocalCovers && !empty($results[0]->coverPathId)) {
                        $this->popularAlbum->image = sprintf(\Spieldose\API::LOCAL_COVER_PATH_SMALL_THUMBNAIL, $results[0]->coverPathId);
                    } elseif (!empty($results[0]->mbId)) {
                        $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                        $url = $cover->getReleaseImageURL($results[0]->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                        $this->popularAlbum->image = sprintf(\Spieldose\API::REMOTE_COVER_URL_SMALL_THUMBNAIL, $url);
                    }
                }
                $query = "
                    SELECT DISTINCT FIT.album, FIT.mb_album_id AS mbId, FIT.year, D.id AS coverPathId
                    FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                    LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                    WHERE FIT.mb_artist_id = :mbid AND FIT.album IS NOT NULL
                    ORDER BY FIT.year DESC
                    LIMIT 1
                ";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                $results = $this->dbh->query($query, $params);
                $this->latestAlbum = (object) [
                    'title' => null,
                    'year' => null,
                    'image' => null
                ];
                if (count($results) == 1) {
                    $this->latestAlbum->title = $results[0]->album;
                    $this->latestAlbum->year = $results[0]->year;
                    if ($useLocalCovers && !empty($results[0]->coverPathId)) {
                        $this->latestAlbum->image = sprintf(\Spieldose\API::LOCAL_COVER_PATH_SMALL_THUMBNAIL, $results[0]->coverPathId);
                    } elseif (!empty($results[0]->mbId)) {
                        $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                        $url = $cover->getReleaseImageURL($results[0]->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                        $this->latestAlbum->image = sprintf(\Spieldose\API::REMOTE_COVER_URL_SMALL_THUMBNAIL, $url);
                    }
                }
                $trackFields = [
                    "id " => "FIT.id",
                    "mbId" => "FIT.mb_release_track_id",
                    "title" => "FIT.title",
                    "artistMBId" => "FIT.mb_artist_id",
                    "artistName" => "COALESCE(CACHE_ARTIST_MUSICBRAINZ.name, FIT.artist)",
                    "releaseMBId" => "FIT.mb_album_id",
                    "releaseTitle" => "COALESCE(MB_CACHE_RELEASE.title, FIT.album)",
                    "albumArtistMBId" => "COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id)",
                    "albumArtistName" => "COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist)",
                    "year" => "COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))",
                    "trackNumber" => "FIT.track_number",
                    "coverPathId" => "D.id"
                ];

                $this->topTracks = $this->getTopTracks($this->dbh, ["mbId" => $this->mbId, "name" => null]);

                $query = sprintf(
                    "
                        SELECT DISTINCT
                            FIT.mb_album_id AS mbId,
                            COALESCE(MB_CACHE_RELEASE.title, FIT.album) AS title,
                            COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist) AS artistName,
                            COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id) AS artistMBId,
                            COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT)) AS year,
                            D.id AS coverPathId
                        FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                        LEFT JOIN CACHE_ARTIST_MUSICBRAINZ ON CACHE_ARTIST_MUSICBRAINZ.mbid = FIT.mb_artist_id
                        WHERE COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id) = :mbid
                        GROUP BY FIT.mb_album_id
                        ORDER BY COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))
                    "
                );
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                $this->topAlbums = $this->dbh->query($query, $params);
                foreach ($this->topAlbums as $album) {
                    $album->artist = new \stdClass();
                    $album->artist->mbId = $album->artistMBId;
                    $album->artist->name = $album->artistName;
                    if ($useLocalCovers && !empty($album->coverPathId)) {
                        $album->covers = [
                            "small" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_SMALL_THUMBNAIL, $album->coverPathId),
                            "normal" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_NORMAL_THUMBNAIL, $album->coverPathId)
                        ];
                    } elseif (!empty($album->mbId)) {
                        $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                        $url = $cover->getReleaseImageURL($album->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                        $album->covers = [
                            "small" => sprintf(\Spieldose\API::REMOTE_COVER_URL_SMALL_THUMBNAIL, $url),
                            "normal" => sprintf(\Spieldose\API::REMOTE_COVER_URL_NORMAL_THUMBNAIL, $url)
                        ];
                    } else {
                        $album->covers = [
                            "small" => null,
                            "normal" => null
                        ];
                    }
                    unset($album->artistMbId);
                    unset($album->artistName);
                    unset($album->coverPathId);
                }
                $query = sprintf(
                    "
                        SELECT DISTINCT
                            FIT.mb_album_id AS mbId,
                            COALESCE(MB_CACHE_RELEASE.title, FIT.album) AS title,
                            COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist) AS artistName,
                            COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id) AS artistMBId,
                            COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT)) AS year,
                            D.id AS coverPathId
                        FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                        LEFT JOIN CACHE_ARTIST_MUSICBRAINZ ON CACHE_ARTIST_MUSICBRAINZ.mbid = FIT.mb_artist_id
                        WHERE FIT.mb_artist_id = :mbid
                        AND COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id) <> :mbid
                        GROUP BY FIT.mb_album_id
                        ORDER BY COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))
                    "
                );
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                $this->appearsOnAlbums = $this->dbh->query($query, $params);
                foreach ($this->appearsOnAlbums as $album) {
                    $album->artist = new \stdClass();
                    $album->artist->mbId = $album->artistMBId;
                    $album->artist->name = $album->artistName;
                    if ($useLocalCovers && !empty($album->coverPathId)) {
                        $album->covers = [
                            "small" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_SMALL_THUMBNAIL, $album->coverPathId),
                            "normal" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_NORMAL_THUMBNAIL, $album->coverPathId)
                        ];
                    } elseif (!empty($album->mbId)) {
                        $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                        $url = $cover->getReleaseImageURL($album->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                        $album->covers = [
                            "small" => sprintf(\Spieldose\API::REMOTE_COVER_URL_SMALL_THUMBNAIL, $url),
                            "normal" => sprintf(\Spieldose\API::REMOTE_COVER_URL_NORMAL_THUMBNAIL, $url)
                        ];
                    } else {
                        $album->covers = [
                            "small" => null,
                            "normal" => null
                        ];
                    }
                    unset($album->artistMbId);
                    unset($album->artistName);
                    unset($album->coverPathId);
                }
                $this->similar = $this->getSimilarArtists($this->dbh, ["mbId" => $this->mbId, "name" => $this->name]);
            } else {
                throw new \Spieldose\Exception\NotFoundException("mbId");
            }
        } else {
            $this->relations = [];
            $this->bio = (object) [
                "source" => null,
                "summary" => null,
                "content" => null
            ];
            $query = "
                SELECT DISTINCT FIT.album, FIT.mb_album_id AS mbId, FIT.year, D.id AS coverPathId
                FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                WHERE FIT.artist = :name AND FIT.album IS NOT NULL
                ORDER BY RANDOM()
                LIMIT 1";
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name)
            );
            $results = $this->dbh->query($query, $params);
            $this->popularAlbum = (object) [
                'title' => null,
                'year' => null,
                'image' => null
            ];
            if (count($results) == 1) {
                $this->popularAlbum->title = $results[0]->album;
                $this->popularAlbum->year = $results[0]->year;
                if ($useLocalCovers && !empty($results[0]->coverPathId)) {
                    $this->popularAlbum->image = sprintf(\Spieldose\API::LOCAL_COVER_PATH_SMALL_THUMBNAIL, $results[0]->coverPathId);
                } elseif (!empty($results[0]->mbId)) {
                    $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                    $url = $cover->getReleaseImageURL($results[0]->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                    $this->popularAlbum->image = sprintf(\Spieldose\API::REMOTE_COVER_URL_SMALL_THUMBNAIL, $url);
                }
            } else {
                throw new \Spieldose\Exception\NotFoundException("artistName");
            }
            $query = "
                SELECT DISTINCT FIT.album, FIT.mb_album_id, FIT.year, D.id AS coverPathId
                FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                WHERE FIT.artist = :name AND FIT.album IS NOT NULL
                ORDER BY FIT.year DESC
                LIMIT 1";
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name)
            );
            $results = $this->dbh->query($query, $params);
            $this->latestAlbum = (object) [
                'title' => null,
                'year' => null,
                'image' => null
            ];
            if (count($results) == 1) {
                $this->latestAlbum->title = $results[0]->album;
                $this->latestAlbum->year = $results[0]->year;
                if ($useLocalCovers && !empty($results[0]->coverPathId)) {
                    $this->latestAlbum->image = sprintf(\Spieldose\API::LOCAL_COVER_PATH_SMALL_THUMBNAIL, $results[0]->coverPathId);
                } elseif (!empty($results[0]->mbId)) {
                    $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                    $url = $cover->getReleaseImageURL($results[0]->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                    $this->latestAlbum->image = sprintf(\Spieldose\API::REMOTE_COVER_URL_SMALL_THUMBNAIL, $url);
                }
            }
            $trackFields = [
                "id " => "FIT.id",
                "mbId" => "FIT.mb_release_track_id",
                "title" => "FIT.title",
                "artistMBId" => "FIT.mb_artist_id",
                "artistName" => "COALESCE(CACHE_ARTIST_MUSICBRAINZ.name, FIT.artist)",
                "releaseMBId" => "FIT.mb_album_id",
                "releaseTitle" => "COALESCE(MB_CACHE_RELEASE.title, FIT.album)",
                "albumArtistMBId" => "COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id)",
                "albumArtistName" => "COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist)",
                "year" => "COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))",
                "trackNumber" => "FIT.track_number",
                "coverPathId" => "D.id"
            ];

            $fields = [];
            foreach ($trackFields as $key => $value) {
                $fields[] = $value . " AS " . $key;
            }
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name)
            );

            $this->topTracks = $this->getTopTracks($this->dbh, ["mbId" => null, "name" => $this->name]);
            $query = sprintf(
                "
                        SELECT DISTINCT
                            FIT.mb_album_id AS mbId,
                            COALESCE(MB_CACHE_RELEASE.title, FIT.album) AS title,
                            COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist, CACHE_ARTIST_MUSICBRAINZ.name, FIT.artist) AS artistName,
                            COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_artist_id, FIT.mb_artist_id) AS artistMBId,
                            COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT)) AS year,
                            D.id AS coverPathId
                        FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                        LEFT JOIN CACHE_ARTIST_MUSICBRAINZ ON CACHE_ARTIST_MUSICBRAINZ.mbid = FIT.mb_artist_id
                        WHERE FIT.artist = :name
                        GROUP BY FIT.mb_album_id
                        ORDER BY COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))
                    "
            );
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name)
            );
            $this->topAlbums = $this->dbh->query($query, $params);
            foreach ($this->topAlbums as $album) {
                $album->artist = new \stdClass();
                $album->artist->mbId = $album->artistMBId;
                $album->artist->name = $album->artistName;
                if ($useLocalCovers && !empty($album->coverPathId)) {
                    $album->covers = [
                        "small" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_SMALL_THUMBNAIL, $album->coverPathId),
                        "normal" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_NORMAL_THUMBNAIL, $album->coverPathId)
                    ];
                } elseif (!empty($album->mbId)) {
                    $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                    $url = $cover->getReleaseImageURL($album->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                    $album->covers = [
                        "small" => sprintf(\Spieldose\API::REMOTE_COVER_URL_SMALL_THUMBNAIL, $url),
                        "normal" => sprintf(\Spieldose\API::REMOTE_COVER_URL_NORMAL_THUMBNAIL, $url)
                    ];
                } else {
                    $album->covers = [
                        "small" => null,
                        "normal" => null
                    ];
                }
                unset($album->artistMbId);
                unset($album->artistName);
                unset($album->coverPathId);
            }
            $this->similar = $this->getSimilarArtists($this->dbh, ["mbId" => $this->mbId, "name" => $this->name]);
        }
    }
}
