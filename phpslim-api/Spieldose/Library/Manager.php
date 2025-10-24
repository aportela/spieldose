<?php

declare(strict_types=1);

namespace Spieldose\Library;

class Manager
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
    }

    public function __destruct() {}

    /**
     * checks for library path existence (returns path id || null)
     */
    private function getLibraryPathId(string $path): ?string
    {
        $results = $this->dbh->query(
            "
                SELECT
                    id
                FROM LIBRARY_PATH
                WHERE path = :path
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":path", realpath($path)),
            ]
        );
        if (count($results) == 1) {
            return ($results[0]->id);
        } else {
            return (null);
        }
    }

    /**
     * this is used for preventing duplicated (children) library paths (ex: adding paths: "c:\music\" && "c:\music\jazz")
     */
    public function isPathContainedOnCurrentLibraryPaths(string $path): bool
    {
        $path = realpath($path);
        $results = $this->dbh->query(
            "
                SELECT
                    path
                FROM LIBRARY_PATH
                WHERE path <> :path
                ORDER BY path
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":path", $path),
            ]
        );
        foreach ($results as $result) {
            // TODO: str_starts_with works with unicode ?
            if (str_starts_with($path, $result->path)) {
                return (true);
            }
        }
        return (false);
    }

    /**
     * add / update library path
     */
    public function addLibraryPath(string $path): string
    {
        $path = realpath($path);
        $pathId = $this->getLibraryPathId($path);
        if (empty($pathId)) {
            $pathId = \Spieldose\Utils::uuidv4();
        }
        $stat = stat($path);
        $this->logger->info("Setting library path", [$pathId, $path]);
        $this->dbh->execute(
            "
                    INSERT INTO LIBRARY_PATH
                        (id, path, ctime, mtime)
                    VALUES
                        (:id, :path, :current_timestamp, :mtime)
                    ON CONFLICT (id) DO
                    UPDATE SET
                        mtime = :mtime;
                ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $pathId),
                new \aportela\DatabaseWrapper\Param\StringParam(":path", $path),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime'])
            ]
        );
        return ($pathId);
    }

    /**
     * remove library path
     */
    public function removeLibraryPath(string $path): bool
    {
        $pathId = $this->getLibraryPathId($path);
        if (! empty($pathId)) {
            $this->logger->info("Removing library path", [$pathId, $path]);
            // DIRECTORY && FILE related rows are deleted on cascade
            $this->dbh->execute(
                "
                    DELETE FROM LIBRARY_PATH
                    WHERE id = :id
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $pathId)
                ]
            );
            return (true);
        } else {
            return (false);
        }
    }

    /**
     * return all library paths
     * @return array<mixed>
     */
    public function getLibraryPaths(): array
    {
        return (
            $this->dbh->query(
                "
                SELECT
                    id, path
                FROM LIBRARY_PATH
                ORDER BY path
            "
            )
        );
    }

    /**
     * @return array<mixed>
     */
    public function getLibraryPathDirectories(string $libraryPathId): array
    {
        return (
            $this->dbh->query(
                "
                    SELECT
                        id, path
                    FROM DIRECTORY
                    WHERE library_path_id = :library_path_id
                    ORDER BY path
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":library_path_id", $libraryPathId)
                ]
            )
        );
    }

    private function getLibraryPathDirectoryId(string $path): ?string
    {
        $results = $this->dbh->query(
            "
                SELECT
                    id
                FROM DIRECTORY
                WHERE path = :path
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":path", realpath($path)),
            ]
        );
        if (count($results) == 1) {
            return ($results[0]->id);
        } else {
            return (null);
        }
    }

    /**
     * @return array<mixed>
     */
    public function getLibraryPathDirectoryFiles(string $libraryPathDirectoryId): array
    {
        return (
            $this->dbh->query(
                "
                    SELECT
                        FILE.id, DIRECTORY.path, FILE.name
                    FROM DIRECTORY
                    INNER JOIN FILE ON FILE.directory_id = DIRECTORY.id
                    WHERE DIRECTORY.id = :directory_id
                    ORDER BY path
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":directory_id", $libraryPathDirectoryId)
                ],
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

    private function getLibraryPathDirectoryFileId(string $directoryId, string $name): ?string
    {
        $results = $this->dbh->query(
            "
                SELECT
                    id
                FROM FILE
                WHERE
                    directory_id = :directory_id
                AND
                    name = :name
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_id", $directoryId),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $name),
            ]
        );
        if (count($results) == 1) {
            return ($results[0]->id);
        } else {
            return (null);
        }
    }

    public function removeLibraryPathDirectoryFile(string $id)
    {
        $this->logger->notice("Removing library path directory file", [$id]);
        $this->dbh->execute(
            "
                DELETE FROM FILE
                WHERE id = :id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $id)
            ]
        );
    }

    public function writeLibraryPathDirectoryFileTags(
        string $libraryPathDirectoryFileId,
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
        $this->logger->notice("Setting library path directory file tags", [$libraryPathDirectoryFileId]);
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $libraryPathDirectoryFileId)
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
        $this->dbh->execute(
            "
                DELETE FROM QUEUE_FILE_ID3_SCAN
                WHERE file_id = :file_id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $libraryPathDirectoryFileId)
            ]
        );
    }

    public function removeLibraryPathDirectoryFileTags($libraryPathDirectoryFileId)
    {
        $this->logger->notice("Removing library path directory file tags", [$libraryPathDirectoryFileId]);
        $this->dbh->execute(
            "
                DELETE FROM FILE_ID3_TAG
                WHERE file_id = :file_id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $libraryPathDirectoryFileId)
            ]
        );
    }
    /**
     * scan (fill DIRECTORY && FILE tables) custom library path
     */
    public function scanLibraryPath(string $pathId, string $path)
    {
        $this->logger->notice("Scanning library path", [$pathId, $path]);
        $path = realpath($path);
        $directories = \Spieldose\Library\FileSystem::getRecursiveDirectories($path);
        foreach ($directories as $directory) {
            $directory = realpath($directory);
            $directoryId = $this->getLibraryPathDirectoryId($directory);
            $this->logger->debug("Propagating library path directory", [$directoryId, $directory]);
            $files = \Spieldose\Library\FileSystem::getDirectoryFiles($directory);
            $totalFiles = count($files);
            // only add directories with supported files
            if ($totalFiles > 0) {
                $this->logger->debug("Found directory files", [$totalFiles]);
                $coverFilename = \Spieldose\Library\FileSystem::getCoverFilename($directory);
                $stat = stat($directory);
                if (empty($directoryId)) {
                    $directoryId = \Spieldose\Utils::uuidv4();
                }
                $this->dbh->execute(
                    "
                        INSERT INTO DIRECTORY
                            (id, library_path_id, path, ctime, mtime, cover_filename)
                        VALUES
                            (:id, :library_path_id, :path, :mtime, :cover_filename)
                        ON CONFLICT (id) DO
                        UPDATE SET
                            library_path_id = :library_path_id,
                            path = :path,
                            mtime = :mtime,
                            cover_filename = :cover_filename
                    ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", $directoryId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":library_path_id", $pathId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":path", realpath($directory)),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":ctime", intval(microtime(true) * 1000)),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime']),
                        !empty($coverFilename) ? new \aportela\DatabaseWrapper\Param\StringParam(":cover_filename", $coverFilename) : new \aportela\DatabaseWrapper\Param\NullParam(":cover_filename")
                    ]
                );
                foreach ($files as $file) {
                    $file = realpath($file);
                    $stat = stat($file);
                    $filename = basename($file);
                    $fileId = $this->getLibraryPathDirectoryFileId($directoryId, $filename);
                    $this->logger->debug("Propagating file", [$fileId, $filename]);
                    if (empty($fileId)) {
                        $fileId = \Spieldose\Utils::uuidv4();
                    }
                    $this->dbh->execute(
                        "
                            INSERT INTO FILE
                                (id, directory_id, name, size, ctime, mtime)
                            VALUES
                                (:id, :directory_id, :name, :size, :mtime)
                            ON CONFLICT (id) DO
                            UPDATE SET
                                directory_id = :directory_id,
                                name = :name,
                                size = :size,
                                mtime = :mtime
                        ",
                        [
                            new \aportela\DatabaseWrapper\Param\StringParam(":id", $fileId),
                            new \aportela\DatabaseWrapper\Param\StringParam(":directory_id", $directoryId),
                            new \aportela\DatabaseWrapper\Param\StringParam(":name", $filename),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":size", filesize($file)),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":ctime", intval(microtime(true) * 1000)),
                            new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime'])
                        ]
                    );
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
            } else if (! empty($directoryId)) {
                $this->logger->debug("No found directory files", [$totalFiles]);
                // existent directory with no files => remove
                $this->dbh->execute(
                    "
                        DELETE FROM DIRECTORY
                        WHERE id = :id
                    ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", $directoryId)
                    ]
                );
            }
        }
    }

    /**
     * scan all library paths
     */
    public function scanLibrary()
    {
        $this->logger->notice("Scanning full library");
        $paths = $this->getLibraryPaths();
        foreach ($paths as $path) {
            $this->scanLibraryPath($path->id, $path->path);
        }
    }

    /**
     * full list (id/path) of library files (used for clean orphaned data)
     * return array<mixed>
     */
    public function getAllLibraryPathDirectoryFiles(): array
    {
        return (
            $this->dbh->query(
                "
                    SELECT
                        FILE.id, DIRECTORY.path, FILE.name
                    FROM FILE
                    INNER JOIN DIRECTORY ON DIRECTORY.ID = FILE.directory_id
                    ORDER BY DIRECTORY.path, FILE.name
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

    /**
     * full list (id/path) of library files (used for clean orphaned data)
     * return array<mixed>
     */
    public function getAllLibraryPathDirectoryFilesQueuedForID3(): array
    {
        return (
            $this->dbh->query(
                "
                    SELECT
                        FILE.id, DIRECTORY.path, FILE.name
                    FROM QUEUE_FILE_ID3_SCAN
                    INNER JOIN FILE ON QUEUE_FILE_ID3_SCAN.file_id = FILE.id
                    INNER JOIN DIRECTORY ON DIRECTORY.ID = FILE.directory_id
                    ORDER BY QUEUE_FILE_ID3_SCAN.ctime
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
                        WHERE FIT.artist = FILE_ID3_TAG.artist
                        AND FIT.mb_artist_id IS NOT NULL
                        LIMIT 1
                    )
                    WHERE mb_artist_id IS NULL AND artist IS NOT NULL
                "
            )
        );
    }
}
