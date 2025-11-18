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

    public function enqueueFile(string $fileId, bool $force)
    {
        $this->logger->debug("ID3Scanner::enqueueFile", [$fileId]);
        $currentTimestamp = intval(microtime(true) * 1000);
        $whereCondition = "
            WHERE
                NOT EXISTS (
                    SELECT
                        1
                    FROM FILE_ID3_TAG
                    WHERE
                        file_id = :file_id
                )
        ";
        $this->dbh->execute(
            sprintf(
                "
                    INSERT INTO QUEUE_FILE_ID3_SCAN
                        SELECT
                            :file_id, :current_timestamp
                    %s
                    ON CONFLICT (file_id) DO
                    UPDATE SET
                        ctime = :current_timestamp
                ",
                ! $force ? $whereCondition : null
            ),
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $fileId),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", $currentTimestamp),
            ]
        );
    }

    private function dequeueFile(string $fileId)
    {
        $this->logger->debug("ID3Scanner::dequeueFile", [$fileId]);
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
        $this->logger->debug("ID3Scanner::getPendingQueue");
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
        ?int $trackOriginalYear,
        ?int $trackNumber,
        ?int $discNumber,
        ?int $playtimeSeconds,
        ?array $artistMBIds,
        ?array $releaseArtistMbIds,
        ?string $trackAlbum,
        ?string $releaseGroupMBId,
        ?string $releaseMBId,
        ?string $releaseTrackMBId,
        ?string $genre,
        ?string $mime,
    ) {
        $this->logger->debug("ID3Scanner::writeLibraryPathDirectoryFileTags", [$fileId]);
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
        if ($trackOriginalYear != null) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":original_year", $trackOriginalYear);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":original_year");
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
        if (!empty($trackAlbum)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":album", $trackAlbum);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":album");
        }
        if (!empty($releaseGroupMBId)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":release_group_mbid", $releaseGroupMBId);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":release_group_mbid");
        }
        if (!empty($releaseMBId)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":release_mbid", $releaseMBId);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":release_mbid");
        }
        if (!empty($releaseTrackMBId)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":release_track_mbid", $releaseTrackMBId);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":release_track_mbid");
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
                    (file_id, title, artist, album_artist, album, year, original_year, track_number, disc_number, playtime_seconds, release_group_mbid, release_mbid, release_track_mbid, genre, mime)
                VALUES (:file_id, :title, :artist, :album_artist, :album, :year, :original_year, :track_number, :disc_number, :playtime_seconds, :release_group_mbid, :release_mbid, :release_track_mbid, :genre, :mime)
                ON CONFLICT (file_id) DO
                UPDATE
                    SET
                        title = :title,
                        artist = :artist,
                        album_artist = :album_artist,
                        album = :album,
                        year = :year,
                        original_year = :original_year,
                        track_number = :track_number,
                        disc_number = :disc_number,
                        playtime_seconds = :playtime_seconds,
                        release_group_mbid = :release_group_mbid,
                        release_mbid = :release_mbid,
                        release_track_mbid = :release_track_mbid,
                        genre = :genre,
                        mime = :mime
                ;
            ",
            $params
        );

        $this->dbh->execute(
            "
                DELETE FROM FILE_ID3_TAG_MUSICBRAINZ_ARTIST
                WHERE file_id = :file_id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $fileId)
            ]
        );
        if (is_array($artistMBIds)) {
            foreach ($artistMBIds as $artistMbId) {
                $this->dbh->execute(
                    "
                    INSERT INTO FILE_ID3_TAG_MUSICBRAINZ_ARTIST
                        (file_id, artist_mbid)
                    VALUES
                        (:file_id, :artist_mbid)
                    ON CONFLICT (file_id, artist_mbid) DO NOTHING
                ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $fileId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $artistMbId)
                    ]
                );
            }
        }

        $this->dbh->execute(
            "
                DELETE FROM FILE_ID3_TAG_MUSICBRAINZ_RELEASE_ARTIST
                WHERE file_id = :file_id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $fileId)
            ]
        );
        if (is_array($releaseArtistMbIds)) {
            foreach ($releaseArtistMbIds as $releaseArtistMbId) {
                $this->dbh->execute(
                    "
                    INSERT INTO FILE_ID3_TAG_MUSICBRAINZ_RELEASE_ARTIST
                        (file_id, artist_mbid)
                    VALUES
                        (:file_id, :artist_mbid)
                    ON CONFLICT (file_id, artist_mbid) DO NOTHING
                ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $fileId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $releaseArtistMbId)
                    ]
                );
            }
        }
    }

    private function removeLibraryPathDirectoryFileTags(string $fileId)
    {
        $this->logger->debug("ID3Scanner::removeLibraryPathDirectoryFileTags", [$fileId]);
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

    public function processPendingQueue(?callable $queueItemScanCallback = null, ?callable $noQueueItemscallback = null): float
    {
        $this->logger->info("ID3Scanner::processPendingQueue");
        $scanStartTime = microtime(true);
        $queuedItems = $this->getPendingQueue();
        $totalQueuedItems = count($queuedItems);
        if ($totalQueuedItems == 0) {
            $this->logger->debug("ID3Scanner::processPendingQueue - Queue is empty");
            if ($queueItemScanCallback != null) {
                call_user_func($noQueueItemscallback);
            }
        } else {
            $this->logger->debug("ID3Scanner::processPendingQueue - Total items: ", [$totalQueuedItems]);
            for ($i = 0; $i < $totalQueuedItems; $i++) {
                if ($queueItemScanCallback != null) {
                    call_user_func($queueItemScanCallback, $queuedItems, $totalQueuedItems, $i);
                }
                $tagsData = $this->id3->getTagsData($queuedItems[$i]->fullPath);
                if ($tagsData != null) {
                    $this->logger->debug("ID3Scanner::processPendingQueue - Saving id3 tags");
                    $this->writeLibraryPathDirectoryFileTags(
                        $queuedItems[$i]->id,
                        $tagsData->trackTitle,
                        $tagsData->trackArtist,
                        $tagsData->albumArtist,
                        $tagsData->trackYear,
                        $tagsData->trackOriginalYear,
                        $tagsData->trackNumber,
                        $tagsData->discNumber,
                        $tagsData->playtimeSeconds,
                        $tagsData->artistMBIds,
                        $tagsData->releaseArtistMBIds,
                        $tagsData->trackAlbum,
                        $tagsData->releaseGroupMBId,
                        $tagsData->releaseMBId,
                        $tagsData->releaseTrackMBId,
                        $tagsData->genre,
                        $tagsData->mime,
                    );
                    $this->dequeueFile($queuedItems[$i]->id);
                } else {
                    $this->logger->debug("ID3Scanner::processPendingQueue - Removing id3 tags");
                    $this->removeLibraryPathDirectoryFileTags($queuedItems[$i]->id);
                }
            };
        }
        return (microtime(true) - $scanStartTime);
    }
}
