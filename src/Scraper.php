<?php

declare(strict_types=1);

namespace Spieldose;

class Scraper
{

    private $dbh;
    private $logger;
    private $id3;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->id3 = new \Spieldose\ID3();
    }

    public function __destruct()
    {
    }

    /*
    public function scrapFileTags(string $filePath = "")
    {
        if (file_exists($filePath)) {
            $this->id3->analyze($filePath);
            $params = array();
            $params[] = (new \Spieldose\Database\DBParam())->str(":id", sha1($filePath));
            $params[] = (new \Spieldose\Database\DBParam())->str(":local_path", $filePath);
            $params[] = (new \Spieldose\Database\DBParam())->str(":base_path", dirname($filePath));
            $params[] = (new \Spieldose\Database\DBParam())->str(":file_name", basename($filePath));
            $trackName = $this->id3->getTrackTitle();
            if (!empty($trackName)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":track_name", $trackName);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":track_name");
            }
            $trackArtist = $this->id3->getTrackArtistName();
            if (!empty($trackArtist)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":track_artist", $trackArtist);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":track_artist");
            }
            $artistMBId = $this->id3->getMusicBrainzArtistId();
            // multiple mbids (divided by "/") not supported
            if (!empty($artistMBId) && strlen($artistMBId) == 36) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":artist_mbid", $artistMBId);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":artist_mbid");
            }
            $trackAlbum = $this->id3->getAlbum();
            if (!empty($trackAlbum)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":album_name", $trackAlbum);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":album_name");
            }
            $albumMBId = $this->id3->getMusicBrainzAlbumId();
            // multiple mbids (divided by "/") not supported
            if (!empty($albumMBId) && strlen($albumMBId) == 36) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":album_mbid", $albumMBId);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":album_mbid");
            }
            $trackNumber = $this->id3->getTrackNumber();
            if (!empty($trackNumber)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":track_number", $trackNumber);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":track_number");
            }
            $discNumber = $this->id3->getDiscNumber();
            if (!empty($discNumber)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":disc_number", $discNumber);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":disc_number");
            }
            $albumArtist = $this->id3->getAlbumArtistName();
            if (!empty($albumArtist)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":album_artist", $albumArtist);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":album_artist");
            }
            $year = $this->id3->getYear();
            if (!empty($year)) {
                $params[] = (new \Spieldose\Database\DBParam())->int(":year", intval($year));
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":year");
            }
            $genre = $this->id3->getGenre();
            if (!empty($genre)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":genre", $genre);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":genre");
            }
            $playtimeSeconds = $this->id3->getPlaytimeSeconds();
            if ($playtimeSeconds > 0) {
                $params[] = (new \Spieldose\Database\DBParam())->int(":playtime_seconds", $playtimeSeconds);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":playtime_seconds");
            }
            $playtimesString = $this->id3->getPlaytimeString();
            if (!empty($playtimeSeconds)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":playtime_string", $playtimesString);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":playtime_string");
            }
            $mimeType = $this->id3->getMimeType();
            if (!empty($mimeType)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":mime", $mimeType);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":mime");
            }
            $this->dbh->execute('
                    REPLACE INTO FILE (
                        id,
                        local_path,
                        base_path,
                        file_name,
                        mime,
                        track_name,
                        track_artist,
                        artist_mbid,
                        album_name,
                        album_mbid,
                        album_artist,
                        disc_number,
                        track_number,
                        year,
                        genre,
                        playtime_seconds,
                        playtime_string,
                        created
                    ) VALUES (
                        :id,
                        :local_path,
                        :base_path,
                        :file_name,
                        :mime,
                        :track_name,
                        :track_artist,
                        :artist_mbid,
                        :album_name,
                        :album_mbid,
                        :album_artist,
                        :disc_number,
                        :track_number,
                        :year,
                        :genre,
                        :playtime_seconds,
                        :playtime_string,
                        strftime("%s", "now")
                    )
                ', $params);
        } else {
            throw new \Spieldose\Exception\NotFoundException("path: " . $filePath);
        }
    }
    */

    public function getPendingArtists()
    {
        $artists = array();
        $query = '
            SELECT
                DISTINCT LOWER(TRIM(ARTIST)) AS artist
                FROM FILE_ID3_TAG
                WHERE MB_ARTIST_ID IS NULL
                AND ARTIST IS NOT NULL
                ORDER BY RANDOM()
        ';
        $results = $this->dbh->query($query);
        $totalArtists = count($results);
        for ($i = 0; $i < $totalArtists; $i++) {
            $artists[] = ucwords($results[$i]->artist);
        }
        return ($artists);
    }

    public function getPendingArtistMBIds()
    {
        $mbIds = array();
        $query = '
            SELECT
                DISTINCT LOWER(FIT.MB_ARTIST_ID) AS mbid
            FROM FILE_ID3_TAG FIT
            WHERE FIT.MB_ARTIST_ID IS NOT NULL
            AND NOT EXISTS
                (SELECT MBID FROM MB_CACHE_ARTIST MCA WHERE MCA.MBID = FIT.MB_ARTIST_ID)
        ';
        $results = $this->dbh->query($query);
        $totalArtists = count($results);
        for ($i = 0; $i < $totalArtists; $i++) {
            $mbIds[] = $results[$i]->mbid;
        }
        return ($mbIds);
    }

    /*
    public function mbArtistScrap($artist)
    {
        $mbArtist = \Spieldose\MusicBrainz\Artist::getFromArtist($artist);
        if (empty($mbArtist->mbId)) {
            $mbIds = \Spieldose\MusicBrainz\Artist::searchMusicBrainzId($artist, 1);
            if (count($mbIds) == 1) {
                $mbArtist->mbId = $mbIds[0];
            }
        }
        if (!empty($mbArtist->mbId)) {
            $mbArtist->save($this->dbh);
            $params = array();
            $params[] = (new \Spieldose\Database\DBParam())->str(":artist_mbid", $mbArtist->mbId);
            $params[] = (new \Spieldose\Database\DBParam())->str(":track_artist", $artist);
            $this->dbh->execute(" UPDATE FILE SET artist_mbid = :artist_mbid WHERE track_artist = :track_artist ", $params);
        }
    }

    */

    public function mbArtistMBIdscrap($mbid)
    {
        $mbArtist = new \aportela\MusicBrainzWrapper\Artist($this->logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON);
        $mbArtist->get($mbid);
        if (!empty($mbArtist->mbId)) {
            $this->logger->info("Caching artist " . $mbArtist->mbId);
            $params = array(
                (new \aportela\DatabaseWrapper\Param\StringParam(":MBID", $mbArtist->mbId)),
                (new \aportela\DatabaseWrapper\Param\StringParam(":ARTIST", $mbArtist->name)),
                (new \aportela\DatabaseWrapper\Param\StringParam(":JSON", $mbArtist->raw))
            );
            $this->dbh->exec("
                    INSERT INTO MB_CACHE_ARTIST
                    (MBID, ARTIST, IMAGE, BIO, JSON)
                    VALUES
                    (:MBID, :ARTIST, NULL, NULL, :JSON)
                ", $params);
        }
    }


    public function getPendingAlbums()
    {
        $albums = array();
        $query = "
            SELECT
                DISTINCT LOWER(TRIM(FIT.ALBUM)) AS album,
                LOWER(TRIM(COALESCE(FIT.ALBUM_ARTIST, FIT.ARTIST))) AS artist
            FROM FILE_ID3_TAG FIT
            WHERE FIT.MB_ALBUM_ID IS NULL
            AND FIT.ALBUM IS NOT NULL
            ORDER BY RANDOM()
        ";
        $results = $this->dbh->query($query);
        $totalAlbums = count($results);
        for ($i = 0; $i < $totalAlbums; $i++) {
            $results[$i]->album = ucwords($results[$i]->album);
            $results[$i]->artist = ucwords($results[$i]->artist);
            $albums[] = $results[$i];
        }
        return ($albums);
    }


    public function getPendingAlbumMBIds()
    {
        $mbIds = array();
        $query = '
                SELECT
                    DISTINCT FIT.MB_ALBUM_ID AS mbid
                FROM FILE_ID3_TAG FIT
                WHERE NOT EXISTS
                    (SELECT MBID FROM MB_CACHE_ALBUM MCA WHERE MCA.MBID = FIT.MB_ALBUM_ID)
                AND FIT.MB_ALBUM_ID IS NOT NULL
                ORDER BY RANDOM()
            ';
        $results = $this->dbh->query($query);
        $totalArtists = count($results);
        for ($i = 0; $i < $totalArtists; $i++) {
            $mbIds[] = $results[$i]->mbid;
        }
        return ($mbIds);
    }

    public function mbAlbumScrap(string $album = "", string $artist = "", string $year = "")
    {
        if (!empty($album) && !empty($artist)) {
            $mbRelease = new \aportela\MusicBrainzWrapper\Release($this->logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON);
            $mbAlbumId = null;
            $results = $mbRelease->search($album, $artist, $year, 1);
            if (count($results) == 1) {
                $mbAlbumId = $results[0]->mbId;
            }
            if (!empty($mbAlbumId)) {
                $this->logger->info("Found album " . $album . " of artist " . $artist . " with MusicBrainzId: " . $mbAlbumId);
                $params = array(
                    (new \aportela\DatabaseWrapper\Param\StringParam(":album_mbid", $mbAlbumId)),
                    (new \aportela\DatabaseWrapper\Param\StringParam(":album_name", mb_strtolower($album))),
                    (new \aportela\DatabaseWrapper\Param\StringParam(":artist", mb_strtolower($artist)))
                );
                $this->dbh->exec("
                    UP DATE FILE_ID3_TAG
                    SET MB_ALBUM_ID = :album_mbid
                    WHERE LOWER(TRIM(ALBUM)) = :album_name
                    AND LOWER(TRIM(COALESCE(ALBUM_ARTIST, ARTIST))) = :artist
                ", $params);
            }
        }
    }


    public function mbAlbumMBIdScrap(string $mbid = "")
    {
        $mbRelease = new \aportela\MusicBrainzWrapper\Release($this->logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON);
        $mbRelease->get($mbid);
        if (!empty($mbRelease->mbId)) {
            $this->logger->info("Caching release " . $mbRelease->mbId);
            print_r($mbRelease->mbId);
            print_r($mbRelease->title);
            print_r($mbRelease->artist);
            print_r($mbRelease->year);
            $params = array(
                (new \aportela\DatabaseWrapper\Param\StringParam(":MBID", $mbRelease->mbId)),
                (new \aportela\DatabaseWrapper\Param\StringParam(":ALBUM", $mbRelease->title)),
                (new \aportela\DatabaseWrapper\Param\StringParam(":ARTIST", $mbRelease->artist)),
                (new \aportela\DatabaseWrapper\Param\StringParam(":YEAR", $mbRelease->year)),
                (new \aportela\DatabaseWrapper\Param\StringParam(":JSON", $mbRelease->raw))
            );
            if (!empty($mbRelease->coverArtArchive->front)) {
                $params[] = (new \aportela\DatabaseWrapper\Param\StringParam(":IMAGE", $mbRelease->coverArtArchive->front));
            } else {
                $params[] = (new \aportela\DatabaseWrapper\Param\NullParam(":IMAGE"));
            }
            $this->dbh->exec("
                    INSERT INTO MB_CACHE_RELEASE
                    (MBID, TITLE, YEAR, ARTIST_MBID, ARTIST_NAME, JSON)
                    VALUES
                    (:MBID, :TITLE, :YEAR, :ARTIST_MBID, :ARTIST_NAME, :JSON)
                ", $params);
        }
    }

    /*

    public function getAllDatabaseFiles(): array
    {
        $results = array();
        $query = " SELECT F.ID AS id, D.PATH AS path, F.NAME as filename FROM FILES F LEFT JOIN DIRECTORIES D ON D.ID = F.DIRECTORY_ID ORDER BY D.PATH, F.NAME ";
        $results = $this->dbh->query($query);
        return ($results);
    }

    public function _removeDatabaseReferences(string $fileId)
    {
        $params = array(
            (new \Spieldose\Database\DBParam())->str(":file_id", $fileId)
        );
        $this->dbh->execute(" DELETE FROM STATS WHERE file_id = :file_id ", $params);
        $this->dbh->execute(" DELETE FROM PLAYLIST_TRACK WHERE file_id = :file_id ", $params);
        $this->dbh->execute(" DELETE FROM LOVED_FILE WHERE file_id = :file_id ", $params);
        $this->dbh->execute(" DELETE FROM FILE WHERE id = :file_id ", $params);
    }

    */

    public function fillMissingMBIdsWithExistent()
    {
        $this->dbh->exec(
            "
                UPDATE FILE_ID3_TAG
                    SET MB_ARTIST_ID = (SELECT TMP.MB_ARTIST_ID FROM FILE_ID3_TAG TMP WHERE TMP.ARTIST = FILE_ID3_TAG.ARTIST AND TMP.MB_ARTIST_ID IS NOT NULL LIMIT 1)
                WHERE FILE_ID3_TAG.ARTIST IS NOT NULL
                AND FILE_ID3_TAG.MB_ARTIST_ID IS NULL
            "
        );
        $this->dbh->exec(
            "
                UPDATE FILE_ID3_TAG
                    SET MB_ALBUM_ID = (SELECT TMP.MB_ALBUM_ID FROM FILE_ID3_TAG TMP WHERE TMP.ALBUM = FILE_ID3_TAG.ALBUM AND TMP.ARTIST = FILE_ID3_TAG.ARTIST AND TMP.MB_ALBUM_ID IS NOT NULL LIMIT 1)
                WHERE FILE_ID3_TAG.ALBUM IS NOT NULL AND FILE_ID3_TAG.ARTIST IS NOT NULL
                AND FILE_ID3_TAG.MB_ALBUM_ID IS NULL
            "
        );
        $this->dbh->exec(
            "
                UPDATE FILE_ID3_TAG
                    SET MB_ALBUM_ARTIST_ID = (SELECT TMP.MB_ALBUM_ARTIST_ID FROM FILE_ID3_TAG TMP WHERE TMP.ALBUM_ARTIST = FILE_ID3_TAG.ALBUM_ARTIST AND TMP.MB_ALBUM_ARTIST_ID IS NOT NULL LIMIT 1)
                WHERE FILE_ID3_TAG.ARTIST IS NOT NULL
                AND FILE_ID3_TAG.MB_ALBUM_ARTIST_ID IS NULL
            "
        );
    }
}
