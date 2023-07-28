<?php

declare(strict_types=1);

namespace Spieldose;

class Scanner
{
    private $dbh;
    private $logger;
    private $id3;

    const VALID_COVER_FILENAMES = '{cover,Cover,COVER,front,Front,FRONT}.{jpg,Jpg,JPG,jpeg,Jpeg,JPEG,png,Png,PNG}';

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->id3 = new \Spieldose\ID3();
    }

    public function __destruct()
    {
    }

    public function scan($filePath)
    {
        $this->logger->debug("Processing file: " . $filePath);
        $stat = stat(dirname($filePath));
        $coverFilename = null;
        foreach (glob(dirname($filePath) . DIRECTORY_SEPARATOR . self::VALID_COVER_FILENAMES, GLOB_BRACE) as $file) {
            $coverFilename = basename(realpath($file)); // get real file "case"
            break;
        }

        $this->dbh->exec(
            " INSERT INTO DIRECTORY (id, path, mtime, cover_filename) VALUES (:id, :path, :mtime, :cover_filename) ON CONFLICT (path) DO UPDATE SET mtime = :mtime, cover_filename = :cover_filename ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":id", (\Ramsey\Uuid\Uuid::uuid4())->toString()),
                new \aportela\DatabaseWrapper\Param\StringParam(":path", dirname($filePath)),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime']),
                !empty($coverFilename) ? new \aportela\DatabaseWrapper\Param\StringParam(":cover_filename", $coverFilename) : new \aportela\DatabaseWrapper\Param\NullParam(":cover_filename")
            )
        );
        $directoryId = $this->dbh->query(
            "SELECT id FROM DIRECTORY WHERE path = :path",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":path", dirname($filePath)),
            )
        )[0]->id;

        $this->dbh->exec(
            " INSERT INTO FILE (id, directory_id, name, mtime) VALUES (:id, :directory_id, :name, :mtime) ON CONFLICT (`directory_id`, `name`) DO UPDATE SET MTIME = :mtime ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":id", (\Ramsey\Uuid\Uuid::uuid4())->toString()),
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_id", $directoryId),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", basename($filePath)),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", filemtime($filePath))
            )
        );

        $fileId = $this->dbh->query(
            "SELECT id FROM FILE WHERE directory_id = :directory_id AND name = :name",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_id", $directoryId),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", basename($filePath)),
            )
        )[0]->id;

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

            $trackAlbum = $this->id3->getAlbum();
            if (!empty($trackAlbum)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":album", $trackAlbum);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":album");
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

    public function cleanUp()
    {
        $results = $this->dbh->query(
            " SELECT FILE.id AS id, (DIRECTORY.path || :directory_separator || FILE.name) AS filePath FROM FILE LEFT JOIN DIRECTORY ON FILE.directory_id = DIRECTORY.id ORDER BY DIRECTORY.path, FILE.name ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_separator", DIRECTORY_SEPARATOR)
            )
        );
        $totalResults = count($results);
        if ($totalResults > 0) {
            $this->logger->debug(sprintf("Validating %d paths", $totalResults));
            foreach ($results as $result) {
                if (!file_exists($result->filePath)) {
                    $this->logger->debug(sprintf("File id: %s - Path not found: %s", $result->id, $result->filePath));
                    $this->dbh->query(
                        " DELETE FROM FILE_ID3_TAG WHERE id = :id ",
                        array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":id", $result->id)
                        )
                    );
                    $this->dbh->query(
                        " DELETE FROM FILE WHERE id = :id ",
                        array(
                            new \aportela\DatabaseWrapper\Param\StringParam(":id", $result->id)
                        )
                    );
                }
            }
        }
        $this->dbh->query(" DELETE FROM DIRECTORY WHERE NOT EXISTS (SELECT DISTINCT directory_id FROM FILE); ", []);
    }
}
