<?php

declare(strict_types=1);

namespace Spieldose\Library\Scanner;

class ID3Scanner
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;
    private \Spieldose\Library\ID3Wrapper $id3;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->id3 = new \Spieldose\Library\ID3Wrapper();
    }

    public function __destruct() {}

    public function enqueueFile(string $fileId)
    {
        $currentTimestamp = intval(microtime(true) * 1000);
        $this->logger->debug("Adding to ID3 scan queue", [$fileId, $currentTimestamp]);
        $this->dbh->execute(
            "
                INSERT INTO QUEUE_FILE_ID3_SCAN
                    (file_id, ctime)
                VALUES
                    (:file_id, :current_timestamp)
                ON CONFLICT (file_id) DO
                UPDATE SET
                    ctime = :current_timestamp
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $fileId),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", $currentTimestamp),
            ]
        );
    }

    private function dequeueFile(string $fileId)
    {
        $this->dbh->execute(
            "
                DELETE FROM QUEUE_FILE_ID3_SCAN
                WHERE
                    file_id = :file_id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $fileId)
            ]
        );
    }

    /**
     *
     * return array<mixed>
     */
    private function getPendingQueue(): array
    {
        return (
            $this->dbh->query(
                "
                    SELECT
                        FILE.id, DIRECTORY.path, FILE.name
                    FROM QUEUE_FILE_ID3_SCAN
                    INNER JOIN FILE ON QUEUE_FILE_ID3_SCAN.file_id = FILE.id
                    INNER JOIN DIRECTORY ON DIRECTORY.ID = FILE.directory_id
                    ORDER BY
                        QUEUE_FILE_ID3_SCAN.ctime
                ",
                [],
                function ($rows) {
                    array_map(
                        function ($item) {
                            $item->fullPath = $item->path . DIRECTORY_SEPARATOR . $item->name;
                            unset($item->name);
                            unset($item->path);
                            return $item;
                        },
                        $rows
                    );
                }
            )
        );
    }

    private function writeLibraryPathDirectoryFileTags(
        string $fileId,
        ?string $trackTitle,
        ?string $trackArtist,
        ?string $albumArtist,
        ?int $trackYear,
        ?int $trackNumber,
        ?int $discNumber,
        ?int $playtimeSeconds,
        ?string $artistMBId,
        ?string $albumArtistMBId,
        ?string $trackAlbum,
        ?string $albumMBId,
        ?string $releaseGroupMBId,
        ?string $releaseTrackMBId,
        ?string $genre,
        ?string $mime,
    ) {
        $this->logger->notice("Setting library path directory file tags", [$fileId]);
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $fileId)
        ];
        if (!empty($trackTitle)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":title", $trackTitle);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":title");
        }
        if (!empty($trackArtist)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":artist", $trackArtist);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":artist");
        }
        if (!empty($albumArtist)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":album_artist", $albumArtist);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":album_artist");
        }
        if ($trackYear != null) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":year", $trackYear);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":year");
        }
        if ($trackNumber != null) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":track_number", $trackNumber);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":track_number");
        }
        if ($discNumber != null) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":disc_number", $discNumber);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":disc_number");
        }
        if ($playtimeSeconds != null) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":playtime_seconds", $playtimeSeconds);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":playtime_seconds");
        }
        if (!empty($artistMBId)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_artist_id", $artistMBId);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_artist_id");
        }
        if (!empty($albumArtistMBId)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_album_artist_id", $albumArtistMBId);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_album_artist_id");
        }
        if (!empty($trackAlbum)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":album", $trackAlbum);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":album");
        }
        if (!empty($albumMBId)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_album_id", $albumMBId);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_album_id");
        }
        if (!empty($releaseGroupMBId)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_release_group_id", $releaseGroupMBId);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_release_group_id");
        }
        if (!empty($releaseTrackMBId)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_release_track_id", $releaseTrackMBId);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_release_track_id");
        }
        if (!empty($genre)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":genre", mb_strtolower($genre));
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":genre");
        }
        if (!empty($mime)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mime", $mime);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mime");
        }
        $this->dbh->execute(
            "
                INSERT INTO FILE_ID3_TAG
                    (file_id, title, artist, album_artist, album, year, track_number, disc_number, playtime_seconds, mb_artist_id, mb_album_artist_id, mb_album_id, mb_release_group_id, mb_release_track_id, genre, mime)
                VALUES (:file_id, :title, :artist, :album_artist, :album, :year, :track_number, :disc_number, :playtime_seconds, :mb_artist_id, :mb_album_artist_id, :mb_album_id, :mb_release_group_id, :mb_release_track_id, :genre, :mime)
                ON CONFLICT (file_id) DO
                UPDATE
                    SET
                        title = :title,
                        artist = :artist,
                        album_artist = :album_artist,
                        album = :album,
                        year = :year,
                        track_number = :track_number,
                        disc_number = :disc_number,
                        playtime_seconds = :playtime_seconds,
                        mb_artist_id = :mb_artist_id,
                        mb_album_artist_id = :mb_album_artist_id,
                        mb_album_id = :mb_album_id,
                        mb_release_group_id = :mb_release_group_id,
                        mb_release_track_id = :mb_release_track_id,
                        genre = :genre,
                        mime = :mime
                ;
            ",
            $params
        );
    }

    private function removeLibraryPathDirectoryFileTags(string $fileId)
    {
        $this->logger->notice("Removing library path directory file tags", [$fileId]);
        $this->dbh->execute(
            "
                DELETE FROM FILE_ID3_TAG
                WHERE
                    file_id = :file_id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $fileId)
            ]
        );
    }

    /**
     * this "hack" is done for skipping some unnecesary musicbrainzscraps, on cases like this example:
     *  1.- You have one or more files with artist name tag FILLED and artist mbId FILLED
     *  2.- You have one or more files with artist name tag FILLED and artist mbId NOT FILLED
     *
     *  Without this, you run normally scraper and files of 2 will be searched from name/s and if we found a mbId will be saved
     *  With this, we update database to match all files of 2 with the mbId of 1 (skip unnecesary scraps)
     */
    public function fixMissingArtistMBIdsWithExistent(): int
    {
        return (
            $this->dbh->exec(
                "
                    UPDATE FILE_ID3_TAG SET mb_artist_id = (
                        SELECT FIT.mb_artist_id
                        FROM FILE_ID3_TAG FIT
                        WHERE
                            FIT.artist = FILE_ID3_TAG.artist
                        AND
                            FIT.mb_artist_id IS NOT NULL
                        LIMIT 1
                    )
                    WHERE
                        mb_artist_id IS NULL
                    AND
                        artist IS NOT NULL
                "
            )
        );
    }

    public function processPendingQueue(?callable $queueItemScanCallback = null): float
    {
        $scanStartTime = microtime(true);
        $queuedItems = $this->getPendingQueue();
        $totalQueuedItems = count($queuedItems);
        for ($i = 0; $i < $totalQueuedItems; $i++) {
            if ($queueItemScanCallback != null) {
                call_user_func($queueItemScanCallback, $queuedItems, $totalQueuedItems, $i);
            }
            $tagsData = $this->id3->getTagsData($queuedItems[$i]->fullPath);
            if ($tagsData != null) {
                $this->writeLibraryPathDirectoryFileTags(
                    $queuedItems[$i]->id,
                    $tagsData->trackTitle,
                    $tagsData->trackArtist,
                    $tagsData->albumArtist,
                    $tagsData->trackYear,
                    $tagsData->trackNumber,
                    $tagsData->discNumber,
                    $tagsData->playtimeSeconds,
                    $tagsData->artistMBId,
                    $tagsData->albumArtistMBId,
                    $tagsData->trackAlbum,
                    $tagsData->albumMBId,
                    $tagsData->releaseGroupMBId,
                    $tagsData->releaseTrackMBId,
                    $tagsData->genre,
                    $tagsData->mime,
                );
                $this->dequeueFile($queuedItems[$i]->id);
            } else {
                $this->removeLibraryPathDirectoryFileTags($queuedItems[$i]->id);
            }
        };
        return (microtime(true) - $scanStartTime);
    }
}
