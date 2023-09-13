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
                $filterConditions[] = sprintf(" COALESCE(MB_CACHE_ARTIST.name, FIT.artist) LIKE %s", $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        } else {
            $filterConditions[] = " COALESCE(MB_CACHE_ARTIST.name, FIT.artist) IS NOT NULL ";
        }
        if (isset($filter["genre"]) && !empty($filter["genre"])) {
            $filterConditions[] = " EXISTS (SELECT MB_CACHE_ARTIST_GENRE.genre FROM MB_CACHE_ARTIST_GENRE WHERE MB_CACHE_ARTIST_GENRE.artist_mbid = FIT.mb_artist_id AND MB_CACHE_ARTIST_GENRE.genre = :genre) ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":genre", $filter["genre"]);
        }
        $fieldDefinitions = [
            "mbId" => "FIT.mb_artist_id",
            "name" => "COALESCE(MB_CACHE_ARTIST.name, FIT.artist)",
            "image" => "MB_CACHE_ARTIST.image",
            "totalTracks" => " COALESCE(TOTAL_TRACKS.total, 0) "
        ];
        $fieldCountDefinition = [
            "totalResults" => " COUNT(DISTINCT FIT.mb_artist_id || COALESCE(MB_CACHE_ARTIST.name, FIT.artist))"
        ];
        $filter = new \aportela\DatabaseBrowserWrapper\Filter();

        $afterBrowseFunction = function ($data) {
            $data->items = array_map(
                function ($result) {
                    if (!empty($result->image)) {
                        $result->image = sprintf(\Spieldose\API::REMOTE_ARTIST_URL_SMALL_THUMBNAIL, urlencode($result->image));
                    }
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
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                LEFT JOIN (
                    SELECT FILE_ID3_TAG.mb_artist_id AS artistMBId, COUNT(*) AS total
                    FROM FILE_ID3_TAG
                    GROUP BY FILE_ID3_TAG.mb_artist_id
                    HAVING FILE_ID3_TAG.mb_artist_id NOT NULL
                ) AS TOTAL_TRACKS ON TOTAL_TRACKS.artistMBId = FIT.mb_artist_id
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
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                %s
            ",
            $browser->getQueryCountFields(),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null
        );
        $data = $browser->launch($query, $queryCount);
        return ($data);
    }

    private function getMBIdFromName(string $name)
    {
        $query = " SELECT mbid FROM MB_CACHE_ARTIST WHERE name = :name ";
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
            "artistName" => "COALESCE(MB_CACHE_ARTIST.name, FIT.artist)",
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
        } else if (isset($filter["name"]) && !empty($filter["name"])) {
            $filterConditions[] = " COALESCE(MB_CACHE_ARTIST.name, FIT.artist) = :name ";
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
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
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

    private function getSimilarArtists(\aportela\DatabaseWrapper\DB $dbh, array $filter): array
    {
        $artistFields = [
            "mbId" => "FIT.mb_artist_id",
            "name" => "COALESCE(MB_CACHE_ARTIST.name, FIT.artist)",
            "image" => "MB_CACHE_ARTIST.image",
            "totalTracks" => " COALESCE(TOTAL_TRACKS.total, 0) "
        ];

        $fields = [];
        foreach ($artistFields as $key => $value) {
            $fields[] = $value . " AS " . $key;
        }

        $params = [];

        $filterConditions = [
            " COALESCE(MB_CACHE_ARTIST.name, FIT.artist) IS NOT NULL "
        ];

        if (isset($filter["mbId"]) && !empty($filter["mbId"])) {
            $filterConditions[] = "
                EXISTS (
                    SELECT * FROM MB_CACHE_ARTIST_GENRE MB1
                    WHERE MB1.artist_mbid <> :mbid
                    AND MB1.artist_mbid = FIT.mb_artist_id
                    AND MB1.genre IN (
                        SELECT MB2.genre FROM MB_CACHE_ARTIST_GENRE MB2
                        WHERE MB2.artist_mbid = :mbid
                    )
                )
            ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $filter["mbId"]);
        } else if (isset($filter["name"]) && !empty($filter["name"])) {
        }

        $query = sprintf(
            "
                SELECT DISTINCT %s
                FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                LEFT JOIN (
                    SELECT FILE_ID3_TAG.mb_artist_id AS artistMBId, COUNT(*) AS total
                    FROM FILE_ID3_TAG
                    GROUP BY FILE_ID3_TAG.mb_artist_id
                    HAVING FILE_ID3_TAG.mb_artist_id NOT NULL
                ) AS TOTAL_TRACKS ON TOTAL_TRACKS.artistMBId = FIT.mb_artist_id
                %s
                ORDER BY RANDOM()
                LIMIT 64
            ",
            implode(", ", $fields),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null
        );

        $similarArtists = $dbh->query($query, $params);
        foreach ($similarArtists as $artist) {
            if (!empty($artist->image)) {
                $artist->image = sprintf(\Spieldose\API::REMOTE_ARTIST_URL_SMALL_THUMBNAIL, urlencode($artist->image));
            }
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
            $query = " SELECT name, image FROM MB_CACHE_ARTIST WHERE mbid = :mbid ";
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
                $query = " SELECT url_relationship_typeid, url_relationship_value FROM MB_CACHE_ARTIST_URL_RELATIONSHIP WHERE artist_mbid = :mbid ";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                $results = $this->dbh->query($query, $params);
                if (count($results) > 0) {
                    foreach ($results as $result) {
                        $this->relations[] = (object) ["type-id" => $result->url_relationship_typeid, "url" => $result->url_relationship_value];
                    }
                } else {
                    $this->relations = [];
                }
                $query = " SELECT DISTINCT genre FROM MB_CACHE_ARTIST_GENRE WHERE artist_mbid = :mbid ORDER BY genre";
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
                $query = " SELECT bio_summary, bio_content FROM MB_LASTFM_CACHE_ARTIST WHERE artist_mbid = :mbid ";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)
                );
                $results = $this->dbh->query($query, $params);
                if (count($results) == 1) {
                    $this->bio = (object) [
                        "summary" => $results[0]->bio_summary,
                        "content" => $results[0]->bio_content
                    ];
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
                    } else if (!empty($results[0]->mbId)) {
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
                    } else if (!empty($results[0]->mbId)) {
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
                    "artistName" => "COALESCE(MB_CACHE_ARTIST.name, FIT.artist)",
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
                        LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
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
                    } else if (!empty($album->mbId)) {
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
                        LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
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
                    } else if (!empty($album->mbId)) {
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
                $this->similar = $this->getSimilarArtists($this->dbh, ["mbId" => $this->mbId, "name" => null]);
            } else {
                throw new \Spieldose\Exception\NotFoundException("mbId");
            }
        } else {

            $this->relations = [];
            $this->bio = (object) [
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
                } else if (!empty($results[0]->mbId)) {
                    $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                    $url = $cover->getReleaseImageURL($results[0]->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                    $this->popularAlbum->image = sprintf(\Spieldose\API::REMOTE_COVER_URL_SMALL_THUMBNAIL, $url);
                }
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
                } else if (!empty($results[0]->mbId)) {
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
                "artistName" => "COALESCE(MB_CACHE_ARTIST.name, FIT.artist)",
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
                            COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist, MB_CACHE_ARTIST.name, FIT.artist) AS artistName,
                            COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_artist_id, FIT.mb_artist_id) AS artistMBId,
                            COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT)) AS year,
                            D.id AS coverPathId
                        FROM FILE_ID3_TAG FIT INNER JOIN FILE F ON F.ID = FIT.id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                        LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
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
                } else if (!empty($album->mbId)) {
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
            $this->similar = $this->getSimilarArtists($this->dbh, ["mbId" => $this->mbId, "name" => null]);
        }
    }
}
