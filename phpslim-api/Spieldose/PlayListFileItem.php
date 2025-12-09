<?php

declare(strict_types=1);

namespace Spieldose;

class PlayListFileItem
{
    public \Spieldose\Entities\File $file;
    public \Spieldose\ThumbnailImages $images;

    public function __construct(\Spieldose\Entities\File $file)
    {
        $this->file = $file;
        $this->images = new \Spieldose\ThumbnailImages();
    }

    public static function getPlayListFileItems(\aportela\DatabaseWrapper\DB $db, int $count = 32): array
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
            $file = new \Spieldose\Entities\File($result->id);
            $file->id = $result->id;
            $file->name = $result->name;
            $file->size = $result->size;
            $file->mime = $result->mime;
            $file->trackInfo = new \stdClass();
            $file->trackInfo->playTimeSeconds = $result->playtime_seconds;
            $file->trackInfo->title = $result->title;
            $file->trackInfo->artist = new \stdClass();
            $file->trackInfo->artist->mbId = null;
            $file->trackInfo->artist->name = $result->artist;
            $file->trackInfo->album = new \stdClass();
            $file->trackInfo->album->mbId = $result->release_mbid;
            $file->trackInfo->album->title = $result->album;
            $file->trackInfo->album->year = intval($result->year);
            $file->trackInfo->album->artist = new \stdClass();
            $file->trackInfo->album->artist->mbId = null;
            $file->trackInfo->album->artist->name = $result->artist;
            $file->trackInfo->imageURL = new \stdClass();
            $file->trackInfo->favorited = $result->ftime ? intval($result->ftime) : null;
            if (! empty($result->cover_filename)) {
                $file->trackInfo->imageURL->small = "api2/local_thumbnail?width=100&height=100&quality=90&pathId=" . $result->directoryPathId;
                $file->trackInfo->imageURL->normal = "api2/local_thumbnail?width=400&height=400&quality=90&pathId=" . $result->directoryPathId;
            } elseif (! empty($result->release_mbid)) {
                $coverUrl = sprintf('https://coverartarchive.org/release/%s/front-500', $result->release_mbid);
                $file->trackInfo->imageURL->small = "api2/remote_thumbnail?width=100&height=100&quality=90&url=" . urlencode($coverUrl);
                $file->trackInfo->imageURL->normal = "api2/remote_thumbnail?width=400&height=400&quality=90&url=" . urlencode($coverUrl);
            } else {
                $file->trackInfo->imageURL->small = null;
                $file->trackInfo->imageURL->normal = null;
            }
            $playListFileItem = new PlayListFileItem($file);
            if (! empty($result->cover_filename)) {
                $playListFileItem->images->small = "api2/local_thumbnail?width=100&height=100&quality=90&pathId=" . $result->directoryPathId;
                $playListFileItem->images->medium = "api2/local_thumbnail?width=400&height=400&quality=90&pathId=" . $result->directoryPathId;
                $playListFileItem->images->big = "api2/local_thumbnail?width=800&height=800&quality=90&pathId=" . $result->directoryPathId;
            } elseif (! empty($result->release_mbid)) {
                $coverUrl = sprintf('https://coverartarchive.org/release/%s/front-500', $result->release_mbid);
                $playListFileItem->images->small = "api2/remote_thumbnail?width=100&height=100&quality=90&url=" . urlencode($coverUrl);
                $playListFileItem->images->medium = "api2/remote_thumbnail?width=400&height=400&quality=90&url=" . urlencode($coverUrl);
                $playListFileItem->images->big = "api2/remote_thumbnail?width=800&height=800&quality=90&url=" . urlencode($coverUrl);
            }
            $playlistItems[] = $playListFileItem;
        }
        return ($playlistItems);
    }
}
