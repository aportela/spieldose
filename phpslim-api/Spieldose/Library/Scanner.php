<?php

declare(strict_types=1);

namespace Spieldose\Library;

class Scanner
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;
    private \Spieldose\Library\ID3Wrapper $id3;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->libraryPath = new \Spieldose\Library\LibraryPath($dbh);
        $this->id3 = new \Spieldose\Library\ID3Wrapper();
    }

    public function __destruct() {}

    public function scanFile(string $libraryPathDirectoryFileId, string $path)
    {
        $this->id3->analyze($path);
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $libraryPathDirectoryFileId)
        ];
        $this->logger->debug("File: ", [$path]);
        if ($this->id3->hasTags()) {
            $trackTitle = $this->id3->getTag(\Spieldose\Library\ID3TAGType::TITLE);
            if (!empty($trackTitle)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":title", $trackTitle);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":title");
            }
            $trackArtist = $this->id3->getTag(\Spieldose\Library\ID3TAGType::TRACK_ARTIST_NAME);
            if (!empty($trackArtist)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":artist", $trackArtist);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":artist");
            }
            $albumArtist = $this->id3->getTag(\Spieldose\Library\ID3TAGType::ALBUM_ARTIST_NAME);
            if (!empty($albumArtist)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":album_artist", $albumArtist);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":album_artist");
            }
            $trackYear = $this->id3->getTag(\Spieldose\Library\ID3TAGType::YEAR);
            if ($trackYear != null) {
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":year", $trackYear);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":year");
            }
            $trackNumber = $this->id3->getTag(\Spieldose\Library\ID3TAGType::TRACK_NUMBER);
            if ($trackNumber != null) {
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":track_number", $trackNumber);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":track_number");
            }
            $discNumber = $this->id3->getTag(\Spieldose\Library\ID3TAGType::DISC_NUMBER);
            if ($discNumber != null) {
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":disc_number", $discNumber);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":disc_number");
            }
            $playtimeSeconds = $this->id3->getTag(\Spieldose\Library\ID3TAGType::PLAYTIME_SECONDS);
            if ($playtimeSeconds != null) {
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":playtime_seconds", $playtimeSeconds);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":playtime_seconds");
            }
            $artistMBId = $this->id3->getTag(\Spieldose\Library\ID3TAGType::MB_ARTIST_ID);
            // multiple mbids (divided by "/") not supported
            if (!empty($artistMBId) && strlen($artistMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_artist_id", $artistMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_artist_id");
            }
            $albumArtistMBId = $this->id3->getTag(\Spieldose\Library\ID3TAGType::MB_ALBUM_ARTIST_ID);;
            // multiple mbids (divided by "/") not supported
            if (!empty($albumArtistMBId) && strlen($albumArtistMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_album_artist_id", $albumArtistMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_album_artist_id");
            }
            $trackAlbum = $this->id3->getTag(\Spieldose\Library\ID3TAGType::ALBUM);
            if (!empty($trackAlbum)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":album", $trackAlbum);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":album");
            }
            $albumMBId = $this->id3->getTag(\Spieldose\Library\ID3TAGType::MB_ALBUM_ID);
            // multiple mbids (divided by "/") not supported
            if (!empty($albumMBId) && strlen($albumMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_album_id", $albumMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_album_id");
            }
            $releaseGroupMBId = $this->id3->getTag(\Spieldose\Library\ID3TAGType::MB_RELEASE_GROUP_ID);
            // multiple mbids (divided by "/") not supported
            if (!empty($releaseGroupMBId) && strlen($releaseGroupMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_release_group_id", $releaseGroupMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_release_group_id");
            }
            $releaseTrackMBId = $this->id3->getTag(\Spieldose\Library\ID3TAGType::MB_RELEASE_TRACK_ID);
            // multiple mbids (divided by "/") not supported
            if (!empty($releaseTrackMBId) && strlen($releaseTrackMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_release_track_id", $releaseTrackMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_release_track_id");
            }
            $genre = $this->id3->getTag(\Spieldose\Library\ID3TAGType::GENRE);
            if (!empty($genre)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":genre", mb_strtolower($genre));
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":genre");
            }
            $mime = $this->id3->getTag(\Spieldose\Library\ID3TAGType::MIME_TYPE);
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
        } else {
            $this->dbh->execute(
                "
                    DELETE FROM FILE_ID3_TAG
                    WHERE file_id = :file_id
                ",
                $params
            );
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
