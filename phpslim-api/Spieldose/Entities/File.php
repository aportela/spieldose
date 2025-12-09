<?php

declare(strict_types=1);

namespace Spieldose\Entities;

use stdClass;

class File
{
    protected \aportela\DatabaseWrapper\DB $dbh;

    public string $filename;

    public int $filesize;

    public string $mime;

    public stdClass $trackInfo;

    public function __construct(public string $id) {}

    public function get(\aportela\DatabaseWrapper\DB $db): void
    {
        $results = $db->query(
            "
                SELECT
                    FILE.name, FILE.size, COALESCE(FILE_ID3_TAG.mime, :default_mime) AS mime, FILE_ID3_TAG.title, FILE_ID3_TAG.playtime_seconds, FILE_ID3_TAG.release_mbid, FILE_ID3_TAG.release_track_mbid, FILE_ID3_TAG.artist, FILE_ID3_TAG.album, COALESCE(FILE_ID3_TAG.original_year, FILE_ID3_TAG.year) AS year, DIRECTORY.id AS directoryPathId, DIRECTORY.cover_filename, FILE_FAVORITE.ftime
                FROM FILE
                LEFT JOIN FILE_ID3_TAG ON FILE_ID3_TAG.file_id = FILE.id
                LEFT JOIN DIRECTORY ON DIRECTORY.id = FILE.directory_id
                LEFT JOIN FILE_FAVORITE ON FILE_FAVORITE.file_id = FILE.id AND FILE_FAVORITE.user_id = :user_id
                WHERE FILE.id = :id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id),
                new \aportela\DatabaseWrapper\Param\StringParam(":default_mime", "application/octet-stream"),
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId()),
            ]
        );
        if (count($results) === 1) {
            $this->filename = $results[0]->name;
            $this->filesize = $results[0]->size;
            $this->mime = $results[0]->mime;
            $this->trackInfo = new \stdClass();
            $this->trackInfo->playTimeSeconds = $results[0]->playtime_seconds;
            $this->trackInfo->title = $results[0]->title;
            $this->trackInfo->artist = new \stdClass();
            $this->trackInfo->artist->mbId = null;
            $this->trackInfo->artist->name = $results[0]->artist;
            $this->trackInfo->album = new \stdClass();
            $this->trackInfo->album->mbId = $results[0]->release_mbid;
            $this->trackInfo->album->title = $results[0]->album;
            $this->trackInfo->album->year = intval($results[0]->year);
            $this->trackInfo->album->artist = new \stdClass();
            $this->trackInfo->album->artist->mbId = null;
            $this->trackInfo->album->artist->name = $results[0]->artist;
            $this->trackInfo->imageURL = new \stdClass();
            if (! empty($results[0]->cover_filename)) {
                $this->trackInfo->imageURL->small = "api2/local_thumbnail?width=100&height=100&quality=90&pathId=" . $results[0]->directoryPathId;
                $this->trackInfo->imageURL->normal = "api2/local_thumbnail?width=400&height=400&quality=90&pathId=" . $results[0]->directoryPathId;
            } elseif (! empty($results[0]->release_mbid)) {
                $coverUrl = sprintf('https://coverartarchive.org/release/%s/front-500', $results[0]->release_mbid);
                $this->trackInfo->imageURL->small = "api2/remote_thumbnail?width=100&height=100&quality=90&url=" . urlencode($coverUrl);
                $this->trackInfo->imageURL->normal = "api2/remote_thumbnail?width=400&height=400&quality=90&url=" . urlencode($coverUrl);
            } else {
                $this->trackInfo->imageURL->small = null;
                $this->trackInfo->imageURL->normal = null;
            }

            $this->trackInfo->favorited = $results[0]->ftime ? intval($results[0]->ftime) : null;
        } else {
            throw new \Spieldose\Exception\NotFoundException("id");
        }
    }

    public static function getRandomPlayList(\aportela\DatabaseWrapper\DB $db, int $count = 32): array
    {
        $results = $db->query(
            "
                SELECT
                    FILE.id, FILE.name, FILE.size, COALESCE(FILE_ID3_TAG.mime, :default_mime) AS mime, FILE_ID3_TAG.title, FILE_ID3_TAG.playtime_seconds, FILE_ID3_TAG.release_mbid, FILE_ID3_TAG.release_track_mbid, FILE_ID3_TAG.artist, FILE_ID3_TAG.album, COALESCE(FILE_ID3_TAG.original_year, FILE_ID3_TAG.year) AS year, DIRECTORY.id AS directoryPathId, DIRECTORY.cover_filename, FILE_FAVORITE.ftime
                FROM FILE
                LEFT JOIN FILE_ID3_TAG ON FILE_ID3_TAG.file_id = FILE.id
                LEFT JOIN DIRECTORY ON DIRECTORY.id = FILE.directory_id
                LEFT JOIN FILE_FAVORITE ON FILE_FAVORITE.file_id = FILE.id AND FILE_FAVORITE.user_id = :user_id
                ORDER BY RANDOM()
                LIMIT :limit
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":default_mime", "application/octet-stream"),
                new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId()),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":limit", $count),

            ]
        );
        $playlistItems = [];
        foreach ($results as $result) {
            $item = new \stdClass();
            $item->id = $result->id;
            $item->filename = $result->name;
            $item->filesize = $result->size;
            $item->mime = $result->mime;
            $item->trackInfo = new \stdClass();
            $item->trackInfo->playTimeSeconds = $result->playtime_seconds;
            $item->trackInfo->title = $result->title;
            $item->trackInfo->artist = new \stdClass();
            $item->trackInfo->artist->mbId = null;
            $item->trackInfo->artist->name = $result->artist;
            $item->trackInfo->album = new \stdClass();
            $item->trackInfo->album->mbId = $result->release_mbid;
            $item->trackInfo->album->title = $result->album;
            $item->trackInfo->album->year = intval($result->year);
            $item->trackInfo->album->artist = new \stdClass();
            $item->trackInfo->album->artist->mbId = null;
            $item->trackInfo->album->artist->name = $result->artist;
            $item->trackInfo->imageURL = new \stdClass();
            if (! empty($result->cover_filename)) {
                $item->trackInfo->imageURL->small = "api2/local_thumbnail?width=100&height=100&quality=90&pathId=" . $result->directoryPathId;
                $item->trackInfo->imageURL->normal = "api2/local_thumbnail?width=400&height=400&quality=90&pathId=" . $result->directoryPathId;
            } elseif (! empty($result->release_mbid)) {
                $coverUrl = sprintf('https://coverartarchive.org/release/%s/front-500', $result->release_mbid);
                $item->trackInfo->imageURL->small = "api2/remote_thumbnail?width=100&height=100&quality=90&url=" . urlencode($coverUrl);
                $item->trackInfo->imageURL->normal = "api2/remote_thumbnail?width=400&height=400&quality=90&url=" . urlencode($coverUrl);
            } else {
                $item->trackInfo->imageURL->small = null;
                $item->trackInfo->imageURL->normal = null;
            }

            $item->trackInfo->favorited = $result->ftime ? intval($result->ftime) : null;
            $playlistItems[] = $item;
        }
        return ($playlistItems);
    }

    /**
     * temporal method
     */
    public function rnd(\aportela\DatabaseWrapper\DB $db): void
    {
        $results = $db->query(
            "
                SELECT
                    FILE.id
                FROM FILE
                ORDER BY RANDOM()
            "
        );
        $this->id = $results[0]->id;
    }
}
