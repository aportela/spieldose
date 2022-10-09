<?php

declare(strict_types=1);

namespace Spieldose;

class Track
{

    public $id;
    public $path;
    public $mime;

    public function __construct(string $id = "")
    {
        $this->id = $id;
    }

    public function __destruct()
    {
    }

    private function exists(\Spieldose\Database\DB $dbh): bool
    {
        return (true);
    }

    public function get(\Spieldose\Database\DB $dbh)
    {
        if (isset($this->id) && !empty($this->id)) {
            $results = $dbh->query("SELECT local_path AS path, mime FROM FILE WHERE id = :id", array(
                (new \Spieldose\Database\DBParam())->str(":id", $this->id)
            ));
            if (count($results) == 1) {
                $this->path = $results[0]->path;
                $this->mime = $results[0]->mime;
            } else {
                throw new \Spieldose\Exception\NotFoundException("id: " . $this->name);
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public static function search(\Spieldose\Database\DB $dbh, int $page = 1, int $resultsPage = 16, array $filter = array(), string $order = "")
    {
        $params = array();
        $whereCondition = "";
        if (isset($filter)) {
            $conditions = array();
            if (isset($filter["text"]) && !empty($filter["text"])) {
                $conditions[] = " ( COALESCE(MBT.track, F.track_name) LIKE :text OR COALESCE(MBA2.artist, F.track_artist) LIKE :text OR COALESCE(MBA1.album, F.album_name) LIKE :text ) ";
                $params[] = (new \Spieldose\Database\DBParam())->str(":text", "%" . $filter["text"] . "%");
            }
            if (isset($filter["artist"]) && !empty($filter["artist"])) {
                $conditions[] = " ( MBA1.artist = :artist OR MBA2.artist = :artist OR F.track_artist = :artist OR F.album_artist = :artist ) ";
                $params[] = (new \Spieldose\Database\DBParam())->str(":artist", $filter["artist"]);
            }
            if (isset($filter["album"]) && !empty($filter["album"])) {
                $conditions[] = " ( MBA1.album = :album OR F.album_name = :album ) ";
                $params[] = (new \Spieldose\Database\DBParam())->str(":album", $filter["album"]);
            }
            if (isset($filter["year"]) && !empty($filter["year"])) {
                $conditions[] = " ( MBA1.year = :year OR F.year = :year ) ";
                $params[] = (new \Spieldose\Database\DBParam())->int(":year", intval($filter["year"]));
            }
            if (isset($filter["playlist"]) && !empty($filter["playlist"])) {
                // TODO: check permissions
                $conditions[] = " EXISTS ( SELECT * FROM PLAYLIST_TRACK WHERE file_id = F.id AND playlist_id = :playlist_id ) ";
                $params[] = (new \Spieldose\Database\DBParam())->str(":playlist_id", $filter["playlist"]);
            }
            if (isset($filter["path"]) && !empty($filter["path"])) {
                $conditions[] = " ( F.base_path = :path ) ";
                $params[] = (new \Spieldose\Database\DBParam())->str(":path", $filter["path"]);
            }
            if (isset($filter["loved"]) && !empty($filter["loved"])) {
                // TODO: check permissions
                $conditions[] = " EXISTS ( SELECT * FROM LOVED_FILE WHERE file_id = F.id AND loved = 1 AND user_id = :user_id ) ";
                $params[] = (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId());
            }
            $whereCondition = count($conditions) > 0 ? " AND " .  implode(" AND ", $conditions) : "";
        }
        $queryCount = '
                SELECT
                    COUNT (F.id) AS total
                FROM FILE F
                LEFT JOIN MB_CACHE_TRACK MBT ON MBT.mbid = F.track_mbid
                LEFT JOIN MB_CACHE_ALBUM MBA1 ON MBA1.mbid = F.album_mbid
                LEFT JOIN MB_CACHE_ARTIST MBA2 ON MBA2.mbid = F.artist_mbid
                WHERE COALESCE(MBT.track, F.track_name) IS NOT NULL
                ' . $whereCondition . '
            ';
        $result = $dbh->query($queryCount, $params);
        $data = new \stdClass();
        $data->actualPage = $page;
        $data->resultsPage = $resultsPage;
        $data->totalResults = $result[0]->total;
        if ($resultsPage > 0) {
            $data->totalPages = ceil($data->totalResults / $resultsPage);
        } else {
            $data->totalPages = $data->totalResults > 0 ? 1 : 0;
            $resultsPage = $data->totalResults;
        }
        $sqlOrder = "";
        if (!empty($order) && $order == "random") {
            $sqlOrder = " ORDER BY RANDOM() ";
        } else {
            if (isset($filter["artist"]) && !empty($filter["artist"])) {
                $sqlOrder = " ORDER BY year ASC, album COLLATE NOCASE ASC, F.track_number ";
            } else {
                $sqlOrder = " ORDER BY F.track_number, COALESCE(MBT.track, F.track_name) COLLATE NOCASE ASC ";
            }
        }
        $params[] = (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId());
        $query = sprintf(
            '
                SELECT DISTINCT
                    F.id,
                    F.track_number AS number,
                    COALESCE(MBT.track, F.track_name) AS title,
                    COALESCE(MBA2.artist, F.track_artist) AS artist,
                    COALESCE(MBA1.album, F.album_name) AS album,
                    F.album_mbid AS albumMBId,
                    album_artist AS albumartist,
                    COALESCE(MBA1.year, F.year) AS year,
                    playtime_seconds AS playtimeSeconds,
                    playtime_string AS playtimeString,
                    COALESCE(MBA1.image, LOCAL_PATH_ALBUM_COVER.id) AS image,
                    genre,
                    mime,
                    COALESCE(LF.loved, 0) AS loved
                FROM FILE F
                LEFT JOIN MB_CACHE_TRACK MBT ON MBT.mbid = F.track_mbid
                LEFT JOIN MB_CACHE_ALBUM MBA1 ON MBA1.mbid = F.album_mbid
                LEFT JOIN MB_CACHE_ARTIST MBA2 ON MBA2.mbid = F.artist_mbid
                LEFT JOIN LOVED_FILE LF ON (LF.file_id = F.id AND LF.user_id = :user_id)
                LEFT JOIN LOCAL_PATH_ALBUM_COVER ON LOCAL_PATH_ALBUM_COVER.base_path = F.base_path
                WHERE COALESCE(MBT.track, F.track_name) IS NOT NULL
                %s
                %s
                LIMIT %d OFFSET %d
                ',
            $whereCondition,
            $sqlOrder,
            $resultsPage,
            $resultsPage * ($page - 1)
        );
        $data->results = $dbh->query($query, $params);
        return ($data);
    }

    public function incPlayCount(\Spieldose\Database\DB $dbh): bool
    {
        if (isset($this->id) && !empty($this->id)) {
            $params[] = (new \Spieldose\Database\DBParam())->str(":file_id", $this->id);
            $params[] = (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId());
            return ($dbh->execute('REPLACE INTO STATS (user_id, file_id, played) VALUES(:user_id, :file_id, CURRENT_TIMESTAMP); ', $params));
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function love(\Spieldose\Database\DB $dbh): bool
    {
        if (isset($this->id) && !empty($this->id)) {
            $params[] = (new \Spieldose\Database\DBParam())->str(":file_id", $this->id);
            $params[] = (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId());
            return ($dbh->execute('REPLACE INTO LOVED_FILE (file_id, user_id, loved) VALUES(:file_id, :user_id, 1); ', $params));
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public function unLove(\Spieldose\Database\DB $dbh): bool
    {
        if (isset($this->id) && !empty($this->id)) {
            $params[] = (new \Spieldose\Database\DBParam())->str(":file_id", $this->id);
            $params[] = (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId());
            return ($dbh->execute('DELETE FROM LOVED_FILE WHERE file_id = :file_id AND user_id = :user_id; ', $params));
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("id");
        }
    }

    public static function searchNew(\aportela\DatabaseWrapper\DB $db, $query)
    {
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":directory_separator", DIRECTORY_SEPARATOR)
        );
        if (!empty($query)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":query", "%" . $query . "%");
        }
        $tracks = $db->query(
            sprintf(
                "
                    SELECT
                        FILES.ID AS id,
                        FILE_ID3_TAG.TITLE AS title,
                        FILE_ID3_TAG.ARTIST AS artist,
                        FILE_ID3_TAG.ALBUM_ARTIST AS albumArtist,
                        FILE_ID3_TAG.ALBUM AS album,
                        FILE_ID3_TAG.YEAR as year,
                        FILE_ID3_TAG.TRACK_NUMBER as trackNumber,
                        FILE_ID3_TAG.DISC_NUMBER AS discNumber,
                        (DIRECTORIES.PATH || :directory_separator || DIRECTORIES.COVER_FILENAME) AS localCoverPath,
                        FILE_ID3_TAG.PLAYTIME_SECONDS AS playtimeSeconds,
                        FILE_ID3_TAG.MB_ARTIST_ID AS musicBrainzArtistId,
                        FILE_ID3_TAG.MB_ALBUM_ARTIST_ID as musicBrainzAlbumArtistId,
                        FILE_ID3_TAG.MB_ALBUM_ID AS musicBrainzAlbumId
                    FROM FILES
                    LEFT JOIN FILE_ID3_TAG ON FILE_ID3_TAG.ID = FILES.ID
                    LEFT JOIN DIRECTORIES ON DIRECTORIES.ID = FILES.DIRECTORY_ID
                    %s
                    ORDER BY %s
                    LIMIT 64
                ",
                //!empty($query) ? " WHERE FILE_ID3_TAG.TITLE LIKE :query OR FILE_ID3_TAG.ALBUM LIKE :query OR FILE_ID3_TAG.ARTIST LIKE :query" : "",
                " WHERE DIRECTORIES.COVER_FILENAME IS NULL AND FILE_ID3_TAG.MB_ALBUM_ID IS NOT NULL ",
                empty($query) ? " RANDOM() " : " FILE_ID3_TAG.ARTIST, FILE_ID3_TAG.ALBUM, FILE_ID3_TAG.DISC_NUMBER, FILE_ID3_TAG.TRACK_NUMBER ",

            ),
            $params
        );
        $tracks = array_map(function ($track) {
            $track->year = $track->year ? intval($track->year) : null;
            //$track->thumbnailURL = "http://localhost:8080/api2/track/thumbnail/400/400/" . $track->id;
            //$track->thumbnailURL = !empty($track->localCoverPath) && file_exists(($track->localCoverPath)) ? "http://localhost:8080/api2/track/thumbnail/400/400/" . $track->id : null;
            if (empty($track->thumbnailURL) && !empty($track->musicBrainzAlbumId)) {
                //$track->thumbnailURL = sprintf("https://coverartarchive.org/release/%s/front-250", $track->musicBrainzAlbumId);
            }
            unset($track->localCoverPath);
            return $track;
        }, $tracks);
        return ($tracks);
    }

    public function getNew(\aportela\DatabaseWrapper\DB $db)
    {
        $data = $db->query(
            "
                SELECT
                    (DIRECTORIES.PATH || :directory_separator || FILES.NAME) AS path
                FROM FILES
                INNER JOIN DIRECTORIES ON DIRECTORIES.ID = FILES.DIRECTORY_ID
                WHERE FILES.ID = :id
            ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_separator", DIRECTORY_SEPARATOR),
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id)
            )
        );
        $this->path = $data[0]->path;
    }

    public static function getLocalThumbnail(\aportela\DatabaseWrapper\DB $db, $id, $width, $height)
    {
        $results = $db->query(
            "
                SELECT
                    (DIRECTORIES.PATH || :directory_separator || DIRECTORIES.COVER_FILENAME) AS localCoverPath
                FROM FILES
                LEFT JOIN DIRECTORIES ON DIRECTORIES.ID = FILES.DIRECTORY_ID
                WHERE FILES.ID = :id
            ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_separator", DIRECTORY_SEPARATOR),
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $id)
            )
        );
        if (count($results) == 1) {
            $localPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "thumbnails";
            $logger = new \Monolog\Logger("remote-thumbnail-cache-wrapper");
            $thumbnail = new \aportela\RemoteThumbnailCacheWrapper\Thumbnail($logger, $localPath);
            if (!empty($results[0]->localCoverPath) && file_exists(($results[0]->localCoverPath))) {
                if ($thumbnail->getFromLocalFilesystem($results[0]->localCoverPath, $width, $height)) {
                    return ($thumbnail->path);
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

    public static function getRemoteThumbnail(\aportela\DatabaseWrapper\DB $db, $id, $width, $height)
    {
        $results = $db->query(
            "
                SELECT
                    FILE_ID3_TAG.MB_ALBUM_ID AS musicBrainzAlbumId
                FROM FILES
                LEFT JOIN FILE_ID3_TAG ON FILE_ID3_TAG.ID = FILES.ID
                WHERE FILES.ID = :id
            ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $id)
            )
        );
        if (count($results) == 1) {
            $localPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "thumbnails";
            $logger = new \Monolog\Logger("remote-thumbnail-cache-wrapper");
            $thumbnail = new \aportela\RemoteThumbnailCacheWrapper\Thumbnail($logger, $localPath);
            $url = null;
            if ($width < 250) {
                $url = sprintf("https://coverartarchive.org/release/%s/front-250", $results[0]->musicBrainzAlbumId);
            } elseif ($width < 500) {
                $url = sprintf("https://coverartarchive.org/release/%s/front-500", $results[0]->musicBrainzAlbumId);
            } else {
                $url = sprintf("https://coverartarchive.org/release/%s/front", $results[0]->musicBrainzAlbumId);
            }
            if ($thumbnail->getFromRemoteURL($url, $width, $height)) {
                return ($thumbnail->path);
            } else {
                return (null);
            }
        } else {
            throw new \Spieldose\Exception\NotFoundException("Invalid musicBrainzAlbumId for id: " . $id);
        }
    }
}
