<?php

declare(strict_types=1);

namespace Spieldose\Entities\PlayList;

final class PlayListFileItem extends PlayListItem
{
    public \Spieldose\Entities\File $file;

    public function __construct(\Spieldose\Entities\File $file)
    {
        parent::__construct();
        $this->file = $file;
    }

    /**
     * return array<Spieldose\Entities\PlayList\PlayListFileItem>
     */
    public static function getPlayListFileItems(\aportela\DatabaseWrapper\DB $db, string $playListId, string $userId): array
    {
        $query = null;
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":default_mime", "application/octet-stream"),
            new \aportela\DatabaseWrapper\Param\StringParam(":playlist_id",  $playListId),

        ];
        // user requested his own favorites playlist
        if ($playListId === $userId) {
            $query = "
                SELECT
                    FILE.id,
                    FILE.name,
                    FILE.size,
                    COALESCE(FILE_ID3_TAG.mime, :default_mime) AS mime,
                    FILE_ID3_TAG.title, FILE_ID3_TAG.playtime_seconds,
                    FILE_ID3_TAG.release_mbid,
                    FILE_ID3_TAG.release_track_mbid,
                    FILE_ID3_TAG.artist,
                    FILE_ID3_TAG.album,
                    COALESCE(FILE_ID3_TAG.original_year, FILE_ID3_TAG.year) AS year,
                    DIRECTORY.id AS directoryPathId,
                    DIRECTORY.cover_filename,
                    NULL AS favorite
                FROM PLAYLIST_FILE
                INNER JOIN FILE ON PLAYLIST_FILE.file_id = FILE.id
                LEFT JOIN DIRECTORY ON DIRECTORY.id = FILE.directory_id
                LEFT JOIN FILE_ID3_TAG ON FILE_ID3_TAG.file_id = FILE.id
                WHERE PLAYLIST_FILE.playlist_id = :playlist_id
                ORDER BY PLAYLIST_FILE.file_index
            ";
        } else {
            // user requested another (not own favorites) playlist
            $query = "
                SELECT
                    FILE.id,
                    FILE.name,
                    FILE.size,
                    COALESCE(FILE_ID3_TAG.mime, :default_mime) AS mime,
                    FILE_ID3_TAG.title, FILE_ID3_TAG.playtime_seconds,
                    FILE_ID3_TAG.release_mbid,
                    FILE_ID3_TAG.release_track_mbid,
                    FILE_ID3_TAG.artist,
                    FILE_ID3_TAG.album,
                    COALESCE(FILE_ID3_TAG.original_year, FILE_ID3_TAG.year) AS year,
                    DIRECTORY.id AS directoryPathId,
                    DIRECTORY.cover_filename,
                    PLAYLIST_FILE_USER_FAV.file_id AS favorite
                FROM PLAYLIST_FILE
                INNER JOIN FILE ON PLAYLIST_FILE.file_id = FILE.id
                LEFT JOIN DIRECTORY ON DIRECTORY.id = FILE.directory_id
                LEFT JOIN FILE_ID3_TAG ON FILE_ID3_TAG.file_id = FILE.id
                LEFT JOIN PLAYLIST_FILE AS PLAYLIST_FILE_USER_FAV ON (
                        PLAYLIST_FILE_USER_FAV.file_id = FILE.id
                    AND
                        PLAYLIST_FILE_USER_FAV.playlist_id = :user_id
                )
                WHERE PLAYLIST_FILE.playlist_id = :playlist_id
                ORDER BY PLAYLIST_FILE.file_index
            ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", $userId);
        }
        $results = $db->query($query, $params);
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
            if ($playListId === $userId) { // requested playlist is user favorites playlist (all items have true value on favorite flag)
                $file->trackInfo->favorited = true;
            } else {
                // another playlist (not user favorites playlist)
                $file->trackInfo->favorited = $result->id === $result->favorite;
            }
            $playListFileItem = new PlayListFileItem($file);
            if (! empty($result->cover_filename)) {
                $playListFileItem->images->setLocal($result->directoryPathId);
            } elseif (! empty($result->release_mbid)) {
                $playListFileItem->images->setRemote(sprintf('https://coverartarchive.org/release/%s/front-500', $result->release_mbid));
            }
            $playlistItems[] = $playListFileItem;
        }
        return ($playlistItems);
    }

    // TODO: required ???
    public static function getPlayListFileItem(\aportela\DatabaseWrapper\DB $db, string $fileId, string $userId): \Spieldose\Entities\PlayList\PlayListFileItem
    {
        $results = $db->query(
            "
                SELECT
                    FILE.id,
                    FILE.name,
                    FILE.size,
                    COALESCE(FILE_ID3_TAG.mime, :default_mime) AS mime,
                    FILE_ID3_TAG.title, FILE_ID3_TAG.playtime_seconds,
                    FILE_ID3_TAG.release_mbid,
                    FILE_ID3_TAG.release_track_mbid,
                    FILE_ID3_TAG.artist,
                    FILE_ID3_TAG.album,
                    COALESCE(FILE_ID3_TAG.original_year, FILE_ID3_TAG.year) AS year,
                    DIRECTORY.id AS directoryPathId,
                    DIRECTORY.cover_filename
                FROM FILE
                LEFT JOIN DIRECTORY ON DIRECTORY.id = FILE.directory_id
                LEFT JOIN FILE_ID3_TAG ON FILE_ID3_TAG.file_id = FILE.id
                WHERE FILE.id = :file_id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":default_mime", "application/octet-stream"),
                new \aportela\DatabaseWrapper\Param\StringParam(":file_id",  $fileId),

            ]
        );
        if (count($results) === 1) {
            $file = new \Spieldose\Entities\File($results[0]->id);
            $file->id = $results[0]->id;
            $file->name = $results[0]->name;
            $file->size = $results[0]->size;
            $file->mime = $results[0]->mime;
            $file->trackInfo = new \stdClass();
            $file->trackInfo->playTimeSeconds = $results[0]->playtime_seconds;
            $file->trackInfo->title = $results[0]->title;
            $file->trackInfo->artist = new \stdClass();
            $file->trackInfo->artist->mbId = null;
            $file->trackInfo->artist->name = $results[0]->artist;
            $file->trackInfo->album = new \stdClass();
            $file->trackInfo->album->mbId = $results[0]->release_mbid;
            $file->trackInfo->album->title = $results[0]->album;
            $file->trackInfo->album->year = intval($results[0]->year);
            $file->trackInfo->album->artist = new \stdClass();
            $file->trackInfo->album->artist->mbId = null;
            $file->trackInfo->album->artist->name = $results[0]->artist;
            $file->trackInfo->imageURL = new \stdClass();
            $file->trackInfo->favorited = false; // TODO:
            $playListFileItem = new PlayListFileItem($file);
            if (! empty($results[0]->cover_filename)) {
                $playListFileItem->images->setLocal($results[0]->directoryPathId);
            } elseif (! empty($results[0]->release_mbid)) {
                $playListFileItem->images->setRemote(sprintf('https://coverartarchive.org/release/%s/front-500', $results[0]->release_mbid));
            }
            return ($playListFileItem);
        } else {
            throw new \Spieldose\Exception\NotFoundException("id");
        }
    }
}
