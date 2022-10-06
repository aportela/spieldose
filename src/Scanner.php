<?php

declare(strict_types=1);

namespace Spieldose;

class Scanner
{
    private $dbh;
    private $logger;
    private $id3;

    const VALID_COVER_FILENAMES = '{cover,Cover,COVER,front,Front,FRONT}.{jpg,Jpg,JPG,jpeg,Jpeg,JPEG,png,Png,PNG}';

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Monolog\Logger $logger)
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
        echo "Scanning " . $filePath . ": ";
        try {

            $stat = stat(dirname($filePath));
            $coverFilename = null;
            foreach (glob(dirname($filePath) . DIRECTORY_SEPARATOR . self::VALID_COVER_FILENAMES, GLOB_BRACE) as $file) {
                $coverFilename = basename(realpath($file)); // get real file "case"
                break;
            }

            if (!empty($coverFilename)) {
                $this->dbh->query(
                    " REPLACE INTO DIRECTORIES (ID, PATH, ATIME, MTIME, COVER_FILENAME) VALUES (:id, :path, STRFTIME('%s'), :mtime, :cover_filename) ",
                    array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", sha1(dirname($filePath))),
                        new \aportela\DatabaseWrapper\Param\StringParam(":path", dirname($filePath)),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime']),
                        new \aportela\DatabaseWrapper\Param\StringParam(":cover_filename", $coverFilename)
                    )
                );
            } else {
                $this->dbh->query(
                    " REPLACE INTO DIRECTORIES (ID, PATH, ATIME, MTIME, COVER_FILENAME) VALUES (:id, :path, STRFTIME('%s'), :mtime, NULL) ",
                    array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", sha1(dirname($filePath))),
                        new \aportela\DatabaseWrapper\Param\StringParam(":path", dirname($filePath)),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime'])
                    )
                );
            }
            $this->dbh->query(
                " REPLACE INTO FILES (ID, DIRECTORY_ID, NAME, ATIME, MTIME) VALUES (:id, :path_id, :name, STRFTIME('%s'), :mtime) ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", sha1($filePath)),
                    new \aportela\DatabaseWrapper\Param\StringParam(":path_id", sha1(dirname($filePath))),
                    new \aportela\DatabaseWrapper\Param\StringParam(":name", basename($filePath)),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", filemtime($filePath))
                )
            );
            $this->id3->analyze($filePath);
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":id", sha1($filePath))
            );
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
            $artistMBId = $this->id3->getMusicBrainzArtistId();
            // multiple mbids (divided by "/") not supported
            if (!empty($artistMBId) && strlen($artistMBId) == 36) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mb_artist_id", $artistMBId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mb_artist_id");
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
            $this->dbh->query(
                "
                    REPLACE INTO FILE_ID3_TAG
                        (ID, TITLE, ARTIST, ALBUM_ARTIST, ALBUM, YEAR, MB_ARTIST_ID, MB_ALBUM_ID)
                    VALUES (:id, :title, :artist, :album_artist, :album, :year, :mb_artist_id, :mb_album_id); ",
                $params
            );
            echo "ok!" . PHP_EOL;
        } catch (\Throwable $e) {
            echo "error!" . PHP_EOL;
        }
    }

    public function cleanUp()
    {
        $this->logger->debug("Starting scanner cleanup");
        $results = $this->dbh->query(
            " SELECT FILES.ID AS id, (DIRECTORIES.PATH || :directory_separator || FILES.NAME) AS filePath FROM FILES LEFT JOIN DIRECTORIES ON FILES.DIRECTORY_ID = DIRECTORIES.ID ORDER BY DIRECTORIES.PATH, FILES.NAME ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_separator", DIRECTORY_SEPARATOR)
            )
        );
        foreach ($results as $result) {
            if (!file_exists($result->filePath)) {
                echo "deleting " . $result->id . PHP_EOL;
                $this->dbh->query(
                    " DELETE FROM FILES WHERE ID = :id ",
                    array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", $result->id)
                    )
                );
            }
        }
    }
}
