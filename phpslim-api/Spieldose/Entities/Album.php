<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Album extends \Spieldose\Entities\Entity
{
    public $mbId;
    public string $title;
    public object $artist;
    public ?int $year;
    public ?string $pathId;
    public array $media;
    public array $covers;

    public function __construct(string $mbId = null, string $title, ?int $year, ?object $artist)
    {
        $this->mbId = $mbId;
        $this->title = $title;
        $this->year = $year;
        $this->artist = $artist;
        $this->media = [];
        $this->covers = [];
    }

    public function get(\aportela\DatabaseWrapper\DB $dbh, bool $useLocalCovers = true, bool $scrap = true)
    {
        if (!empty($this->mbId)) {
            $query = "
                SELECT title, year, artist_mbid, artist_name, media_count, TMP_COVER_PATH.coverPathId
                FROM CACHE_RELEASE_MUSICBRAINZ
                LEFT JOIN (
                    SELECT DIRECTORY.id AS coverPathId
                    FROM FILE_ID3_TAG
                    INNER JOIN FILE ON FILE.id = FILE_ID3_TAG.id
                    INNER JOIN DIRECTORY ON DIRECTORY.id = FILE.directory_id
                    WHERE FILE_ID3_TAG.mb_album_id = :mbid
                    AND DIRECTORY.cover_filename IS NOT NULL
                    LIMIT 1
                ) TMP_COVER_PATH
                WHERE mbid = :mbid
            ";
            $params = [new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)];
            $releaseResults = $dbh->query($query, $params);
            if (count($releaseResults) == 1) {
                $this->title = $releaseResults[0]->title;
                $this->year = $releaseResults[0]->year;
                $this->artist = (object) ["mbId" => $releaseResults[0]->artist_mbid, "name" => $releaseResults[0]->artist_name];
                if ($useLocalCovers && !empty($releaseResults[0]->coverPathId)) {
                    $this->covers = [
                        "small" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_SMALL_THUMBNAIL, $releaseResults[0]->coverPathId),
                        "normal" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_NORMAL_THUMBNAIL, $releaseResults[0]->coverPathId)
                    ];
                } else {
                    $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                    $url = $cover->getReleaseImageURL($this->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                    $this->covers = [
                        "small" => sprintf(\Spieldose\API::REMOTE_COVER_URL_SMALL_THUMBNAIL, $url),
                        "normal" => sprintf(\Spieldose\API::REMOTE_COVER_URL_NORMAL_THUMBNAIL, $url)
                    ];
                }
                $query = "
                    SELECT position, track_count
                    FROM CACHE_RELEASE_MUSICBRAINZ_MEDIA
                    WHERE release_mbid = :mbid
                ";
                $params = [new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId)];
                $mediaResults = $dbh->query($query, $params);
                foreach ($mediaResults as $mediaResult) {
                    $query = "
                        SELECT
                            FILE_ID3_TAG.id AS id, CACHE_RELEASE_MUSICBRAINZ_MEDIA_TRACK.position, CACHE_RELEASE_MUSICBRAINZ_MEDIA_TRACK.mbid, CACHE_RELEASE_MUSICBRAINZ_MEDIA_TRACK.title, CACHE_RELEASE_MUSICBRAINZ_MEDIA_TRACK.artist_mbid, CACHE_RELEASE_MUSICBRAINZ_MEDIA_TRACK.artist_name, CACHE_RELEASE_MUSICBRAINZ_MEDIA_TRACK.length
                        FROM CACHE_RELEASE_MUSICBRAINZ_MEDIA_TRACK
                        LEFT JOIN FILE_ID3_TAG ON FILE_ID3_TAG.mb_release_track_id = CACHE_RELEASE_MUSICBRAINZ_MEDIA_TRACK.mbid
                        WHERE CACHE_RELEASE_MUSICBRAINZ_MEDIA_TRACK.release_mbid = :mbid
                        AND CACHE_RELEASE_MUSICBRAINZ_MEDIA_TRACK.release_media = :media
                        ORDER BY CACHE_RELEASE_MUSICBRAINZ_MEDIA_TRACK.position
                    ";
                    $params = [
                        new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":media", $mediaResult->position)
                    ];
                    $tracks = [];
                    foreach ($dbh->query($query, $params) as $track) {
                        $tracks[] = (object) [
                            "id" => $track->id,
                            "position" => $track->position,
                            "mbId" => $track->mbid,
                            "title" => $track->title,
                            "artist" => (object) [
                                "mbId" => $track->artist_mbid,
                                "name" => $track->artist_name,
                            ],
                            "length" => $track->length
                        ];
                    }
                    $this->media[$mediaResult->position - 1] = ["tracks" => $tracks];
                }
            } else if ($scrap) {
                $release = new \Spieldose\Scraper\Release\MusicBrainz(
                    new \Psr\Log\NullLogger(),
                    \aportela\MusicBrainzWrapper\APIFormat::JSON
                );
                $release->mbId = $this->mbId;
                if ($release->scrap()) {
                    $release->saveCache($dbh);
                    $this->get($dbh, $useLocalCovers, false);
                } else {
                    throw new \Spieldose\Exception\NotFoundException("mbid");
                }
            } else {
                throw new \Spieldose\Exception\NotFoundException("mbid");
            }
        } else if (!empty($this->title) && !empty($this->artist->name)) {
            $query = "
                        SELECT
                            FILE_ID3_TAG.id, track_number AS position, mb_release_track_id AS mbid, title, mb_artist_id AS artist_mbid, artist AS artist_name, (playtime_seconds * 100) AS length, COALESCE(disc_number, 1) AS disc_number, DIRECTORY.id AS coverPathId
                        FROM FILE_ID3_TAG
                        INNER JOIN FILE ON FILE.id = FILE_ID3_TAG.id
                        LEFT JOIN DIRECTORY ON DIRECTORY.id = FILE.directory_id AND DIRECTORY.cover_filename IS NOT NULL
                        WHERE FILE_ID3_TAG.album = :title
                        AND FILE_ID3_TAG.album_artist = :artistName
                        AND FILE_ID3_TAG.year = :year
                        ORDER BY COALESCE(disc_number, 1), track_number
                    ";
            $params = [
                new \aportela\DatabaseWrapper\Param\StringParam(":title", $this->title),
                new \aportela\DatabaseWrapper\Param\StringParam(":artistName", $this->artist->name),
            ];
            if (!empty($this->year)) {
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":year", $this->year);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":year");
            }
            $discNumbers = [];
            $trackResults = $dbh->query($query, $params);
            $coverPathId = null;
            foreach ($trackResults as $result) {
                if (!in_array($result->disc_number, $discNumbers)) {
                    $discNumbers[] = $result->disc_number;
                }
                if (empty($coverPathId) && !empty($result->coverPathId)) {
                    $coverPathId = $result->coverPathId;
                }
            }
            if ($useLocalCovers && !empty($coverPathId)) {
                $this->covers = [
                    "small" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_SMALL_THUMBNAIL, $coverPathId),
                    "normal" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_NORMAL_THUMBNAIL, $coverPathId)
                ];
            } else {
                $this->covers = [
                    "small" => null,
                    "normal" => null
                ];
            }
            foreach ($discNumbers as $discNumber) {
                $tracks = [];
                foreach ($trackResults as $track) {
                    if ($track->disc_number == $discNumber) {
                        $tracks[] = (object) [
                            "id" => $track->id,
                            "position" => $track->position,
                            "mbId" => $track->mbid,
                            "title" => $track->title,
                            "artist" => (object) [
                                "mbId" => $track->artist_mbid,
                                "name" => $track->artist_name,
                            ],
                            "length" => $track->length
                        ];
                    }
                }
                $this->media[$discNumber - 1] = ["tracks" => $tracks];
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("mbid,title,artist");
        }
    }

    public static function getAlbumLocalPathCoverFromPathId(\aportela\DatabaseWrapper\DB $dbh, string $pathId): ?string
    {
        $params = [new \aportela\DatabaseWrapper\Param\StringParam(":pathId", $pathId)];
        $results = $dbh->query("SELECT D.path AS localCoverPath, D.cover_filename AS localCoverFilename FROM DIRECTORY D WHERE D.id = :pathId", $params);
        if (count($results) == 1) {
            if ($results[0]->localCoverPath && $results[0]->localCoverFilename) {
                $localPath = $results[0]->localCoverPath . DIRECTORY_SEPARATOR . $results[0]->localCoverFilename;
                return ($localPath);
            }
        }
        return (null);
    }

    public static function search(\aportela\DatabaseWrapper\DB $dbh, array $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager, bool $useLocalCovers): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $params = array();
        $filterConditions = array(
            " COALESCE(MB_CACHE_RELEASE.title, FIT.album) IS NOT NULL "
        );
        if (isset($filter["title"]) && !empty($filter["title"])) {
            $words = explode(" ", trim($filter["title"]));
            foreach ($words as $word) {
                $paramName = ":title_" . uniqid();
                $filterConditions[] = sprintf(" COALESCE(MB_CACHE_RELEASE.title, FIT.album) LIKE %s", $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        }
        if (isset($filter["albumArtistName"]) && !empty($filter["albumArtistName"])) {
            $words = explode(" ", trim($filter["albumArtistName"]));
            foreach ($words as $word) {
                $paramName = ":albumArtistName_" . uniqid();
                $filterConditions[] = sprintf(" COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist) LIKE %s", $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        }
        if (isset($filter["text"]) && !empty($filter["text"])) {
            $words = explode(" ", trim($filter["text"]));
            foreach ($words as $word) {
                $paramName = ":text_" . uniqid();
                $filterConditions[] = sprintf(" ( COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist) LIKE %s OR COALESCE(MB_CACHE_RELEASE.title, FIT.album) LIKE %s )", $paramName, $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        }
        $fieldDefinitions = [
            "mbId" => "FIT.mb_album_id",
            "title" => "COALESCE(MB_CACHE_RELEASE.title, FIT.album)",
            "albumArtistName" => "COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist)",
            "albumArtistMbId" => "COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id)",
            "year" => "COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))",
            "coverPathId" => "D.id"
        ];

        $fieldCountDefinition = [
            "totalResults" => " COUNT(*)"
        ];

        $afterBrowseFunction = function ($data) use ($useLocalCovers) {
            $data->items = array_map(
                function ($result) use ($useLocalCovers) {
                    $result->artist = new \stdClass();
                    $result->artist->mbId = $result->albumArtistMbId;
                    $result->artist->name = $result->albumArtistName;
                    unset($result->albumArtistMbId);
                    unset($result->albumArtistName);
                    if ($useLocalCovers && !empty($result->coverPathId)) {
                        $result->covers = [
                            "small" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_SMALL_THUMBNAIL, $result->coverPathId),
                            "normal" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_NORMAL_THUMBNAIL, $result->coverPathId)
                        ];
                    } elseif (!empty($result->mbId)) {
                        $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                        $url = $cover->getReleaseImageURL($result->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                        $result->covers = [
                            "small" => sprintf(\Spieldose\API::REMOTE_COVER_URL_SMALL_THUMBNAIL, $url),
                            "normal" => sprintf(\Spieldose\API::REMOTE_COVER_URL_NORMAL_THUMBNAIL, $url)
                        ];
                    } else {
                        $result->covers = [
                            "small" => null,
                            "normal" => null
                        ];
                    }
                    unset($result->coverPathId);
                    // create a unique hash for this element, this is done because artist name && artist musicbrainz are not both mandatory and can not be used as key on vue v-for
                    $result->hash = md5($result->mbId . $result->title);
                    return ($result);
                },
                $data->items
            );
        };

        $browser = new \aportela\DatabaseBrowserWrapper\Browser($dbh, $fieldDefinitions, $fieldCountDefinition, $pager, $sort, new \aportela\DatabaseBrowserWrapper\Filter(), $afterBrowseFunction);

        foreach ($params as $param) {
            $browser->addDBQueryParam($param);
        }

        $query = sprintf(
            "
                SELECT %s
                FROM FILE_ID3_TAG FIT
                INNER JOIN FILE F ON F.ID = FIT.id
                LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                %s
                GROUP BY FIT.mb_album_id, COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist), COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id), COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))
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
                SELECT %s
                FROM (
                    SELECT %s
                    FROM FILE_ID3_TAG FIT
                    INNER JOIN FILE F ON F.ID = FIT.id
                    LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                    LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                    %s
                    GROUP BY FIT.mb_album_id, COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist), COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id), COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))
                )

            ",
            $browser->getQueryCountFields(),
            $browser->getQueryFields(),
            count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
        );
        $data = $browser->launch($query, $queryCount);
        return ($data);
    }

    public static function getTrackIds(\aportela\DatabaseWrapper\DB $dbh, array $filter): array
    {
        $params = [];
        $whereConditions = [];
        if (isset($filter["mbId"]) && !empty($filter["mbId"])) {
            $whereConditions[] = " FIT.mb_album_id = :mbid ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $filter["mbId"]);
        } else if (isset($filter["title"]) && !empty($filter["title"]) && isset($filter["artistName"]) && !empty($filter["artistName"])) {
            $whereConditions[] = " FIT.album = :title ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":title", $filter["title"]);
            $whereConditions[] = " COALESCE(FIT.album_artist, FIT.artist) = :artistName ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":artistName", $filter["artistName"]);
            if (isset($filter["year"]) && !empty($filter["year"])) {
                $whereConditions[] = " FIT.year = :year ";
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":year", $filter["year"]);
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("filter");
        }
        $query = sprintf(
            "
                SELECT F.id
                FROM FILE_ID3_TAG FIT
                INNER JOIN FILE F ON F.ID = FIT.id
                LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                %s
                ORDER BY FIT.track_number
            ",
            count($whereConditions) ? " WHERE " . implode(" AND ", $whereConditions) : null
        );
        $data = $dbh->query($query, $params);
        $ids = [];
        foreach ($data as $item) {
            $ids[] = $item->id;
        }
        return ($ids);
    }
}
