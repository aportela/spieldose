<?php

declare(strict_types=1);

namespace Spieldose\Library;

class Scanner
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;
    private \Spieldose\Library\LibraryPath $libraryPath;
    private \Spieldose\Library\ID3Wrapper $id3;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->libraryPath = new \Spieldose\Library\LibraryPath($dbh);
        $this->id3 = new \Spieldose\Library\ID3Wrapper();
    }

    public function __destruct() {}

    private function scanFile(string $libraryPathDirectoryFileId, string $path)
    {
        $this->id3->analyze($path);
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $libraryPathDirectoryFileId)
        ];
        $this->logger->debug("Current file: ", [$path]);
        if ($this->id3->isTagged()) {
            $trackTitle = $this->id3->getTrackTitle();
            if (!empty($trackTitle)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":title", $trackTitle);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":title");
            }
            $trackArtist = $this->id3->getTrackArtistName();
            if (!empty($trackArtist)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":artist", $trackArtist);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":artist");
            }
            $albumArtist = $this->id3->getAlbumArtistName();
            if (!empty($albumArtist)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":album_artist", $albumArtist);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":album_artist");
            }
            $trackYear = $this->id3->getYear();
            if (!empty($trackYear)) {
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":year", intval($trackYear));
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":year");
            }
            $trackNumber = $this->id3->getTrackNumber();
            if (!empty($trackNumber)) {
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":track_number", intval($trackNumber));
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":track_number");
            }
            $discNumber = $this->id3->getDiscNumber();
            if (!empty($discNumber)) {
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":disc_number", intval($discNumber));
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":disc_number");
            }
            $playtimeSeconds = $this->id3->getPlaytimeSeconds();
            if (!empty($playtimeSeconds)) {
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":playtime_seconds", intval($playtimeSeconds));
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":playtime_seconds");
            }
            $artistMBId = $this->id3->getMusicBrainzArtistId();
            // multiple mbids (divided by "/") not supported
            if (!empty($artistMBId) && strlen($artistMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_artist_id", $artistMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_artist_id");
            }
            $albumArtistMBId = $this->id3->getMusicBrainzAlbumArtistId();
            // multiple mbids (divided by "/") not supported
            if (!empty($albumArtistMBId) && strlen($albumArtistMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_album_artist_id", $albumArtistMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_album_artist_id");
            }
            $trackAlbum = $this->id3->getAlbum();
            if (!empty($trackAlbum)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":album", $trackAlbum);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":album");
            }
            $albumMBId = $this->id3->getMusicBrainzAlbumId();
            // multiple mbids (divided by "/") not supported
            if (!empty($albumMBId) && strlen($albumMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_album_id", $albumMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_album_id");
            }
            $releaseGroupMBId = $this->id3->getMusicBrainzReleaseGroupId();
            // multiple mbids (divided by "/") not supported
            if (!empty($releaseGroupMBId) && strlen($releaseGroupMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_release_group_id", $releaseGroupMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_release_group_id");
            }
            $releaseTrackMBId = $this->id3->getMusicBrainzReleaseTrackId();
            // multiple mbids (divided by "/") not supported
            if (!empty($releaseTrackMBId) && strlen($releaseTrackMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_release_track_id", $releaseTrackMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_release_track_id");
            }
            $genre = $this->id3->getGenre();
            if (!empty($genre)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":genre", $genre);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":genre");
            }
            $mime = $this->id3->getMimeType();
            if (!empty($mime)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mime", $mime);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mime");
            }
            $this->dbh->query(
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
        } else {
            $this->dbh->query(
                "
                    DELETE FROM FILE_ID3_TAG
                    WHERE file_id = :file_id
                ",
                $params
            );
        }
    }

    private function scanDirectory(string $libraryPathDirectoryId)
    {
        $lbDirectoryFiles = $this->libraryPath->getLibraryPathDirectoryFiles($libraryPathDirectoryId);
        $this->logger->debug("Files: ", $lbDirectoryFiles);
        foreach ($lbDirectoryFiles as $lbDirectoryFile) {
            $this->scanFile($lbDirectoryFile->id, $lbDirectoryFile->fullPath);
        }
    }

    private function scanLibraryPath(string $libraryPathId)
    {
        $lbDirectories = $this->libraryPath->getLibraryPathDirectories($libraryPathId);
        $this->logger->debug("Directories: ", $lbDirectories);
        foreach ($lbDirectories as $lbDirectory) {
            $this->scanDirectory($lbDirectory->id);
        }
    }

    /**
     * scan library
     */
    public function scan()
    {
        $lbPaths = $this->libraryPath->getPaths();
        $this->logger->debug("Library paths: ", $lbPaths);
        foreach ($lbPaths as $lbPath) {
            $this->scanLibraryPath($lbPath->id);
        }
    }

    private function saveFile(string $directoryId, string $fileName, int $mtime): string
    {
        $this->dbh->exec(
            " INSERT INTO FILE (id, directory_id, name, mtime, added_timestamp) VALUES (:id, :directory_id, :name, :mtime, strftime('%s', 'now')) ON CONFLICT (`directory_id`, `name`) DO UPDATE SET MTIME = :mtime ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":id", (\Ramsey\Uuid\Uuid::uuid7())->toString()),
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_id", $directoryId),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $fileName),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $mtime)
            )
        );
        $fileId = $this->dbh->query(
            "SELECT id FROM FILE WHERE directory_id = :directory_id AND name = :name",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_id", $directoryId),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $fileName),
            )
        )[0]->id;
        return ($fileId);
    }

    private function saveTags(string $filePath, string $fileId): void
    {
        $this->id3->analyze($filePath);
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":id", $fileId)
        );
        if ($this->id3->isTagged()) {
            $trackTitle = $this->id3->getTrackTitle();
            if (!empty($trackTitle)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":title", $trackTitle);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":title");
            }
            $trackArtist = $this->id3->getTrackArtistName();
            if (!empty($trackArtist)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":artist", $trackArtist);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":artist");
            }
            $albumArtist = $this->id3->getAlbumArtistName();
            if (!empty($albumArtist)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":album_artist", $albumArtist);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":album_artist");
            }
            $trackYear = $this->id3->getYear();
            if (!empty($trackYear)) {
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":year", intval($trackYear));
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":year");
            }
            $trackNumber = $this->id3->getTrackNumber();
            if (!empty($trackNumber)) {
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":track_number", intval($trackNumber));
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":track_number");
            }
            $discNumber = $this->id3->getDiscNumber();
            if (!empty($discNumber)) {
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":disc_number", intval($discNumber));
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":disc_number");
            }
            $playtimeSeconds = $this->id3->getPlaytimeSeconds();
            if (!empty($playtimeSeconds)) {
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":playtime_seconds", intval($playtimeSeconds));
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":playtime_seconds");
            }
            $artistMBId = $this->id3->getMusicBrainzArtistId();
            // multiple mbids (divided by "/") not supported
            if (!empty($artistMBId) && strlen($artistMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_artist_id", $artistMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_artist_id");
            }
            $albumArtistMBId = $this->id3->getMusicBrainzAlbumArtistId();
            // multiple mbids (divided by "/") not supported
            if (!empty($albumArtistMBId) && strlen($albumArtistMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_album_artist_id", $albumArtistMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_album_artist_id");
            }
            $trackAlbum = $this->id3->getAlbum();
            if (!empty($trackAlbum)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":album", $trackAlbum);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":album");
            }
            $albumMBId = $this->id3->getMusicBrainzAlbumId();
            // multiple mbids (divided by "/") not supported
            if (!empty($albumMBId) && strlen($albumMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_album_id", $albumMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_album_id");
            }
            $releaseGroupMBId = $this->id3->getMusicBrainzReleaseGroupId();
            // multiple mbids (divided by "/") not supported
            if (!empty($releaseGroupMBId) && strlen($releaseGroupMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_release_group_id", $releaseGroupMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_release_group_id");
            }
            $releaseTrackMBId = $this->id3->getMusicBrainzReleaseTrackId();
            // multiple mbids (divided by "/") not supported
            if (!empty($releaseTrackMBId) && strlen($releaseTrackMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_release_track_id", $releaseTrackMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_release_track_id");
            }
            $genre = $this->id3->getGenre();
            if (!empty($genre)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":genre", $genre);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":genre");
            }
            $mime = $this->id3->getMimeType();
            if (!empty($mime)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mime", $mime);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mime");
            }
            $this->dbh->query(
                "
                    REPLACE INTO FILE_ID3_TAG
                        (id, title, artist, album_artist, album, year, track_number, disc_number, playtime_seconds, mb_artist_id, mb_album_artist_id, mb_album_id, mb_release_group_id, mb_release_track_id, genre, mime)
                    VALUES (:id, :title, :artist, :album_artist, :album, :year, :track_number, :disc_number, :playtime_seconds, :mb_artist_id, :mb_album_artist_id, :mb_album_id, :mb_release_group_id, :mb_release_track_id, :genre, :mime);
                ",
                $params
            );
        } else {
            $this->dbh->query(
                "
                    DELETE FROM FILE_ID3_TAG
                    WHERE id = :id
                ",
                $params
            );
        }
    }

    // TODO: scan all saved paths if null

    // TODO: make private
    public function scan_old(?string $filePath = null): void
    {
        $this->logger->debug("Processing file: " . $filePath);
        if (!empty($filePath)) {
            if (file_exists($filePath)) {
                $directoryPath = dirname($filePath);
                $directoryId = $this->saveDirectory($directoryPath);
                $fileId = $this->saveFile($directoryId, basename($filePath), filemtime($filePath));
                $this->saveTags($filePath, $fileId);
            } else {
                throw new \Spieldose\Exception\NotFoundException("Path not found: " . $filePath);
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("Empty path");
        }
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
        $query = "
            UPDATE FILE_ID3_TAG SET mb_artist_id = (
                SELECT FIT.mb_artist_id
                FROM FILE_ID3_TAG FIT
                WHERE FIT.artist = FILE_ID3_TAG.artist
                AND FIT.mb_artist_id IS NOT NULL
                LIMIT 1
            )
            WHERE mb_artist_id IS NULL AND artist IS NOT NULL
        ";
        return ($this->dbh->exec($query));
    }

    public function cleanUp(): void
    {
        $results = $this->dbh->query(
            " SELECT FILE.id AS id, (DIRECTORY.path || :directory_separator || FILE.name) AS filePath FROM FILE INNER JOIN DIRECTORY ON FILE.directory_id = DIRECTORY.id ORDER BY DIRECTORY.path, FILE.name ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_separator", DIRECTORY_SEPARATOR)
            )
        );
        $totalResults = count($results);
        if ($totalResults > 0) {
            $this->logger->debug(sprintf("Validating %d files", $totalResults));
            foreach ($results as $result) {
                // file not found
                if (!file_exists($result->filePath)) {
                    $this->logger->debug(sprintf("File id: %s - Path not found: %s", $result->id, $result->filePath));
                    // delete tag entry
                    $this->dbh->query(
                        " DELETE FROM FILE_ID3_TAG WHERE id = :id ",
                        array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":id", $result->id)
                        )
                    );
                    // delete file entry
                    $this->dbh->query(
                        " DELETE FROM FILE WHERE id = :id ",
                        array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":id", $result->id)
                        )
                    );
                }
            }
        }
        // delete all empty directories
        $this->dbh->query(" DELETE FROM DIRECTORY WHERE NOT EXISTS (SELECT DISTINCT directory_id FROM FILE); ", []);
    }
}
