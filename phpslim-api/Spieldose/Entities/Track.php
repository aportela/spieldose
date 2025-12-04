<?php

declare(strict_types=1);

namespace Spieldose\Entities;

class Track extends \Spieldose\Entities\Entity
{
    public ?string $url;
    public object $artist;

    public object $album;
    public array $covers;
     // TODO: change to boolean
    public ?string $lyrics = null;

    public function __construct(public string $id, ?string $mbId = null, public ?string $title = null, ?string $artistMBId = null, ?string $artistName = null, ?string $albumMBId = null, ?string $albumTitle = null, ?string $albumArtistMBId = null, ?string $albumArtistName = null, ?int $year = null, public ?int $trackNumber = null, ?string $coverPathId = null, public ?int $favorited = null)
    {
        $this->url = sprintf(\Spieldose\API::FILE_URL, $this->id);
        $this->mbId = $mbId;
        $this->artist = new \stdClass();
        $this->artist->mbId = $artistMBId;
        $this->artist->name = $artistName;

        $this->album = new \stdClass();
        $this->album->mbId = $albumMBId;
        $this->album->title = $albumTitle;
        $this->album->year = $year;
        $this->album->artist = new \stdClass();
        $this->album->artist->mbId = $albumArtistMBId;
        $this->album->artist->name = $albumArtistName;
        if (!in_array($coverPathId, [null, '', '0'], true)) {
            $this->covers = [
                "small" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_SMALL_THUMBNAIL, $coverPathId),
                "normal" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_NORMAL_THUMBNAIL, $coverPathId),
            ];
        } elseif (!in_array($this->album->mbId, [null, '', '0'], true)) {
            $coverArtArchive = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
            $this->covers = [
                "small" => sprintf(\Spieldose\API::REMOTE_COVER_URL_SMALL_THUMBNAIL, urlencode($coverArtArchive->getReleaseImageURL($this->album->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL))),
                "normal" => sprintf(\Spieldose\API::REMOTE_COVER_URL_NORMAL_THUMBNAIL, urlencode($coverArtArchive->getReleaseImageURL($this->album->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL))),
            ];
        } else {
            $this->covers = [
                "small" => null,
                "normal" => null,
            ];
        }
    }

    public function __destruct() {}

    public function get(\aportela\DatabaseWrapper\DB $db): void
    {
        $fieldDefinitions = [
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
            "favorited" => "FF.favorited",
        ];
        $fields = [];
        foreach ($fieldDefinitions as $alias => $field) {
            $fields[] = sprintf("%s as %s", $field, $alias);
        }

        $query = sprintf(
            "
                SELECT
                %s
                FROM FILE_ID3_TAG FIT
                INNER JOIN FILE F ON F.id = FIT.id
                LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                LEFT JOIN FILE_FAVORITE FF ON FF.file_id = FIT.id AND FF.user_id = :user_id
                WHERE F.id = :id
            ",
            $fields !== [] ? implode(", ", $fields) : null
        );
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
            new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId()),
        ];
        $results = $db->query($query, $params);
        if (count($results) === 1) {
            $this->id = $results[0]->id;
            $this->url = sprintf(\Spieldose\API::FILE_URL, $this->id);
            $this->mbId = $results[0]->mbId;
            $this->title = $results[0]->title;
            $this->artist = new \stdClass();
            $this->artist->mbId = $results[0]->artistMBId;
            $this->artist->name = $results[0]->artistName;
            $this->album = new \stdClass();
            $this->album->mbId = $results[0]->releaseMBId;
            $this->album->title = $results[0]->releaseTitle;
            $this->album->year = $results[0]->year;
            $this->album->artist = new \stdClass();
            $this->album->artist->mbId = $results[0]->albumArtistMBId;
            $this->album->artist->name = $results[0]->albumArtistName;
            $this->trackNumber = $results[0]->trackNumber;
            if (!empty($results[0]->coverPathId)) {
                $this->covers = [
                    "small" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_SMALL_THUMBNAIL, $results[0]->coverPathId),
                    "normal" => sprintf(\Spieldose\API::LOCAL_COVER_PATH_NORMAL_THUMBNAIL, $results[0]->coverPathId),
                ];
            } elseif (!empty($this->album->mbId)) {
                $coverArtArchive = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
                $this->covers = [
                    "small" => sprintf(\Spieldose\API::REMOTE_COVER_URL_SMALL_THUMBNAIL, urlencode($coverArtArchive->getReleaseImageURL($this->album->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL))),
                    "normal" => sprintf(\Spieldose\API::REMOTE_COVER_URL_NORMAL_THUMBNAIL, urlencode($coverArtArchive->getReleaseImageURL($this->album->mbId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL))),
                ];
            } else {
                $this->covers = [
                    "small" => null,
                    "normal" => null,
                ];
            }

            $this->favorited = $results[0]->favorited;
            if (!in_array($this->title, [null, '', '0'], true) && !empty($this->artist->name)) {
                // TODO: custom logger
                $lyrics = new \Spieldose\Lyrics(new \Psr\Log\NullLogger());
                try {
                    $this->lyrics = $lyrics->get($db, $this->title, $this->artist->name) ? $lyrics->lyrics : null;
                } catch (\Throwable) {
                    // TODO: register error ?
                }
            } else {
                $this->lyrics = null;
            }
        } else {
            throw new \Spieldose\Exception\NotFoundException("id");
        }
    }

    public static function search(\aportela\DatabaseWrapper\DB $db, array $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): \aportela\DatabaseBrowserWrapper\BrowserResults
    {
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId()),
        ];
        $filterConditions = [];
        $leftJoins = [];
        if (isset($filter["title"]) && !empty($filter["title"])) {
            $words = explode(" ", trim((string) $filter["title"]));
            foreach ($words as $word) {
                $paramName = ":title_" . uniqid();
                $filterConditions[] = sprintf(" FIT.title LIKE %s", $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        }

        if (isset($filter["artistMBId"]) && !empty($filter["artistMBId"])) {
            $paramName = ":artistMBId";
            $filterConditions[] = sprintf(" FIT.mb_artist_id = %s", $paramName);
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, $filter["artistMBId"]);
        }

        if (isset($filter["artistName"]) && !empty($filter["artistName"])) {
            $words = explode(" ", trim((string) $filter["artistName"]));
            foreach ($words as $word) {
                $paramName = ":artistname_" . uniqid();
                $filterConditions[] = sprintf(" COALESCE(MB_CACHE_ARTIST.name, FIT.artist) LIKE %s", $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        }

        if (isset($filter["text"]) && !empty($filter["text"])) {
            $words = explode(" ", trim((string) $filter["text"]));
            foreach ($words as $word) {
                $paramName = ":text_" . uniqid();
                $filterConditions[] = sprintf(" (FIT.title LIKE %s OR COALESCE(MB_CACHE_ARTIST.name, FIT.artist) LIKE %s OR COALESCE(MB_CACHE_RELEASE.title, FIT.album) LIKE %s) ", $paramName, $paramName, $paramName);
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam($paramName, "%" . trim($word) . "%");
            }
        }

        if (isset($filter["path"]) && !empty($filter["path"])) {
            $filterConditions[] = " EXISTS (SELECT DIRECTORY.id FROM FILE INNER JOIN DIRECTORY ON DIRECTORY.id = FILE.directory_id WHERE FILE.id = F.id AND DIRECTORY.id = :path) ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":path", $filter["path"]);
        }

        if (isset($filter["playlistId"]) && !empty($filter["playlistId"])) {
            if ($filter["playlistId"] == \Spieldose\Playlist::FAVORITE_TRACKS_PLAYLIST_ID) {
                $filterConditions[] = " EXISTS ( SELECT * FROM FILE_FAVORITE WHERE FILE_FAVORITE.user_id = :user_id AND FILE_FAVORITE.file_id = F.id ) ";
            } else {
                $filterConditions[] = " EXISTS (SELECT PT.playlist_id FROM PLAYLIST_TRACK PT WHERE PT.playlist_id = :playlist_id AND PT.track_id = F.id) ";
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id", $filter["playlistId"]);
                $leftJoins[] = " INNER JOIN PLAYLIST_TRACK ON PLAYLIST_TRACK.playlist_id = :playlist_id AND PLAYLIST_TRACK.track_id = F.id ";
            }
        }

        if (isset($filter["currentPlaylistId"]) && !empty($filter["currentPlaylistId"])) {
            $filterConditions[] = " EXISTS (SELECT CPT.playlist_id FROM CURRENT_PLAYLIST_TRACK CPT WHERE CPT.playlist_id = :currentPlaylistId AND CPT.track_id = F.id) ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":currentPlaylistId", $filter["currentPlaylistId"]);
            $leftJoins[] = " INNER JOIN CURRENT_PLAYLIST_TRACK ON CURRENT_PLAYLIST_TRACK.playlist_id = :currentPlaylistId AND CURRENT_PLAYLIST_TRACK.track_id = F.id ";
        }

        if (isset($filter["albumMbId"]) && !empty($filter["albumMbId"])) {
            $filterConditions[] = " FIT.mb_album_id = :mb_album_id ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_album_id", $filter["albumMbId"]);
        }

        if (isset($filter["albumTitle"]) && !empty($filter["albumTitle"])) {
            $filterConditions[] = " COALESCE(MB_CACHE_RELEASE.title, FIT.album) = :album_title ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":album_title", $filter["albumTitle"]);
        }

        if (isset($filter["year"]) && !empty($filter["year"])) {
            $filterConditions[] = " COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT)) = :year ";
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":year", $filter["year"]);
        }

        $fieldDefinitions = [
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
            "favorited" => "FF.favorited",
        ];
        if ((isset($filter["path"]) && !empty($filter["path"])) || (count($sort->items) === 1 && $sort->items[0]::class == "aportela\DatabaseBrowserWrapper\SortItem" && $sort->items[0]->field === "filename")) {
            $fieldDefinitions["filename"] = "F.name";
        }

        if (isset($filter["playlistId"]) && !empty($filter["playlistId"])) {
            if ($filter["playlistId"] == \Spieldose\Playlist::FAVORITE_TRACKS_PLAYLIST_ID) {
                $fieldDefinitions["playListTrackIndex"] = "FF.favorited";
            } else {
                $fieldDefinitions["playListTrackIndex"] = "PLAYLIST_TRACK.track_index";
            }
        } elseif (isset($filter["currentPlaylistId"]) && !empty($filter["currentPlaylistId"])) {
            $fieldDefinitions["currentPlaylistTrackIndex"] = "CURRENT_PLAYLIST_TRACK.track_index";
        }

        $fieldCountDefinition = [
            "totalResults" => " COUNT(FIT.id)",
        ];

        $afterBrowseFunction = function ($data): void {
            $data->items = array_map(
                fn($result): \Spieldose\Entities\Track => new \Spieldose\Entities\Track(
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
                ),
                $data->items
            );
        };

        $browser = new \aportela\DatabaseBrowserWrapper\Browser($db, $fieldDefinitions, $fieldCountDefinition, $pager, $sort, new \aportela\DatabaseBrowserWrapper\Filter(), $afterBrowseFunction);
        foreach ($params as $param) {
            $browser->addDBQueryParam($param);
        }

        $query = sprintf(
            "
                SELECT
                %s
                FROM FILE_ID3_TAG FIT
                INNER JOIN FILE F ON F.id = FIT.id
                LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                LEFT JOIN FILE_FAVORITE FF ON FF.file_id = FIT.id AND FF.user_id = :user_id
                %s
                %s
                %s
                %s
            ",
            $browser->getQueryFields(),
            $leftJoins !== [] ? implode(PHP_EOL, $leftJoins) : null,
            $filterConditions !== [] ? " WHERE " . implode(" AND ", $filterConditions) : null,
            $browser->getQuerySort(),
            $pager->getQueryLimit()
        );
        $queryCount = sprintf(
            "
                SELECT
                    %s
                FROM FILE_ID3_TAG FIT
                INNER JOIN FILE F ON F.id = FIT.id
                LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                LEFT JOIN FILE_FAVORITE FF ON FF.file_id = FIT.id AND FF.user_id = :user_id
                %s
            ",
            $browser->getQueryCountFields(),
            $filterConditions !== [] ? " WHERE " . implode(" AND ", $filterConditions) : null
        );
        return ($browser->launch($query, $queryCount));
    }

    public function increasePlayCount(\aportela\DatabaseWrapper\DB $db): void
    {
        if ($this->id !== '' && $this->id !== '0') {
            $query = " INSERT OR IGNORE INTO FILE_PLAYCOUNT_STATS (file_id, user_id, play_timestamp) VALUES (:file_id, :user_id, strftime('%s', 'now')) ";
            $params = [
                new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $this->id),
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId()),
            ];
            $db->execute($query, $params);
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function toggleFavorite(\aportela\DatabaseWrapper\DB $db, bool $flag): void
    {
        if ($this->id !== '' && $this->id !== '0') {
            $query = null;
            $params = [
                new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $this->id),
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId()),
            ];
            if ($flag) {
                $query = " INSERT INTO FILE_FAVORITE (file_id, user_id, ftime) VALUES (:file_id, :user_id, :current_timestamp) ON CONFLICT (file_id, user_id) DO UPDATE SET ftime = :current_timestamp ";
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000));
            } else {
                $query = " DELETE FROM FILE_FAVORITE WHERE file_id = :file_id AND user_id = :user_id ";
            }

            $db->execute($query, $params);
            if ($flag) {
                $query = " SELECT ftime FROM FILE_FAVORITE WHERE file_id = :file_id AND user_id = :user_id ";
                $data = $db->query(
                    $query,
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $this->id),
                        new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId()),
                    ]
                );
                $this->favorited = count($data) === 1 ? intval($data[0]->ftime) : null;
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    /**
     * @return mixed[]
     */
    public static function getRandomTrackIds(\aportela\DatabaseWrapper\DB $db, int $count = 32): array
    {
        $query = " SELECT F.id FROM FILE F ORDER BY RANDOM() LIMIT :count ";
        $params = [
            new \aportela\DatabaseWrapper\Param\IntegerParam(":count", $count),
        ];
        $results = $db->query($query, $params);
        $ids = [];
        foreach ($results as $result) {
            $ids[] = $result->id;
        }

        return ($ids);
    }

    public static function getLocalThumbnail(\aportela\DatabaseWrapper\DB $db, \Psr\Log\LoggerInterface $logger, string $id, int $quality = \aportela\RemoteThumbnailCacheWrapper\JPEGThumbnail::DEFAULT_IMAGE_QUALITY, int $width = 300, int $height = 300): ?string
    {
        $results = $db->query(
            "
                SELECT
                    (DIRECTORY.path || :directory_separator || DIRECTORY.cover_filename) AS localCoverPath
                FROM FILE
                INNER JOIN DIRECTORY ON DIRECTORY.id = FILE.directory_id
                WHERE FILE.id = :id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_separator", DIRECTORY_SEPARATOR),
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $id),
            ]
        );
        if (count($results) === 1) {
            // TODO: get from settings
            $localPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "thumbnails";
            if (!empty($results[0]->localCoverPath) && file_exists(($results[0]->localCoverPath))) {
                $jpegThumbnail = new \aportela\RemoteThumbnailCacheWrapper\JPEGThumbnail($logger, $localPath);
                $jpegThumbnail->setDimensions($width, $height);
                $jpegThumbnail->setQuality($quality);
                if ($jpegThumbnail->getFromLocalFilesystem($results[0]->localCoverPath)) {
                    return ($jpegThumbnail->path);
                } else {
                    return (null);
                }
            } else {
                return (null);
            }
        } else {
            throw new \Spieldose\Exception\NotFoundException("Invalid path for id: " . $id);
        }
    }

    public static function getRemoteThumbnail(\aportela\DatabaseWrapper\DB $db, \Psr\Log\LoggerInterface $logger, string $id, int $quality = \aportela\RemoteThumbnailCacheWrapper\JPEGThumbnail::DEFAULT_IMAGE_QUALITY, int $width = 300, int $height = 300): ?string
    {
        $results = $db->query(
            "
                SELECT
                    FILE_ID3_TAG.mb_album_id AS musicBrainzAlbumId
                FROM FILE
                LEFT JOIN FILE_ID3_TAG ON FILE_ID3_TAG.id = FILE.id
                WHERE FILE.ID = :id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $id),
            ]
        );
        if (count($results) === 1) {
            $localPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "thumbnails";
            $jpegThumbnail = new \aportela\RemoteThumbnailCacheWrapper\JPEGThumbnail($logger, $localPath);
            $jpegThumbnail->setDimensions($width, $height);
            $jpegThumbnail->setQuality($quality);
            $url = null;
            if ($width < 250) {
                $url = sprintf("https://coverartarchive.org/release/%s/front-250", $results[0]->musicBrainzAlbumId);
            } elseif ($width < 500) {
                $url = sprintf("https://coverartarchive.org/release/%s/front-500", $results[0]->musicBrainzAlbumId);
            } else {
                $url = sprintf("https://coverartarchive.org/release/%s/front", $results[0]->musicBrainzAlbumId);
            }

            if ($jpegThumbnail->getFromRemoteURL($url)) {
                return ($jpegThumbnail->path);
            } else {
                return (null);
            }
        } else {
            throw new \Spieldose\Exception\NotFoundException("Invalid musicBrainzAlbumId for id: " . $id);
        }
    }
}
