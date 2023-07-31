<?php

declare(strict_types=1);

namespace Spieldose;

class Scraper
{

    private $dbh = null;
    private $logger;
    private $id3 = null;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->id3 = new \Spieldose\ID3();
    }

    public function __destruct()
    {
    }

    public function getPendingArtists()
    {
        $artists = array();
        $query = " SELECT DISTINCT artist FROM FILE_ID3_TAG WHERE mb_artist_id IS NULL AND artist IS NOT NULL ORDER BY RANDOM() ";
        $results = $this->dbh->query($query);
        $totalArtists = count($results);
        for ($i = 0; $i < $totalArtists; $i++) {
            $artists[] = $results[$i]->artist;
        }
        return ($artists);
    }

    public function getPendingArtistMBIds()
    {
        $mbIds = array();
        $query = '
                SELECT
                    DISTINCT FIT.mb_artist_id AS mbid
                FROM FILE_ID3_TAG FIT
                WHERE FIT.mb_artist_id IS NOT NULL
                AND NOT EXISTS
                    (SELECT mbid FROM MB_CACHE_ARTIST MCA WHERE MCA.mbid = FIT.mb_artist_id)

                UNION

                SELECT
                    DISTINCT FIT.mb_album_artist_id AS mbid
                FROM FILE_ID3_TAG FIT
                WHERE FIT.mb_album_artist_id IS NOT NULL
                AND NOT EXISTS
                    (SELECT mbid FROM MB_CACHE_ARTIST MCA WHERE MCA.mbid = FIT.mb_album_artist_id)
            ';
        $results = $this->dbh->query($query);
        $totalResults = count($results);
        for ($i = 0; $i < $totalResults; $i++) {
            $mbIds[] = $results[$i]->mbid;
        }
        return ($mbIds);
    }

    public function mbArtistScrap($artist)
    {
        $mbArtist = new \aportela\MusicBrainzWrapper\Artist($this->logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON);
        $results = $mbArtist->search($artist, 1);
        if (count($results) == 1 && !empty($results[0]->mbId)) {
            $query = "
                UPDATE FILE_ID3_TAG SET mb_artist_id = :mbid WHERE mb_artist_id IS NULL AND artist = :artist
            ";
            $results = $this->dbh->exec($query, array(
                new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $results[0]->mbId),
                new \aportela\DatabaseWrapper\Param\StringParam(":artist", $artist),
            ));
        }
    }

    public function mbArtistMBIdscrap($mbId)
    {
        $mbArtist = new \aportela\MusicBrainzWrapper\Artist($this->logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON);
        $mbArtist->get($mbId);
        if (!empty($mbArtist->mbId) && !empty($mbArtist->name)) {
            $this->dbh->exec(
                "
                    INSERT INTO MB_CACHE_ARTIST (mbid, name, image, json) VALUES (:mbid, :name, :image, :json)
                    ON CONFLICT(mbid) DO
                        UPDATE SET name = :name, image = :image, json = :json
                ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $mbArtist->mbId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":name", $mbArtist->name),
                    new \aportela\DatabaseWrapper\Param\NullParam(":image"),
                    new \aportela\DatabaseWrapper\Param\StringParam(":json", $mbArtist->raw)
                )
            );
        }
    }

    public function getPendingAlbums()
    {
        $albums = array();
        $query = "
            SELECT DISTINCT COALESCE(album_artist, artist) AS artist, album, year
            FROM FILE_ID3_TAG
            WHERE mb_album_id IS NULL AND (artist IS NOT NULL OR album_artist IS NOT NULL) AND album IS NOT NULL
            ORDER BY RANDOM()
        ";
        $results = $this->dbh->query($query);
        $totalAlbums = count($results);
        for ($i = 0; $i < $totalAlbums; $i++) {
            $album = new \stdClass();
            $album->artist = $results[$i]->artist;
            $album->name = $results[$i]->album;
            $album->year = $results[$i]->year;
            $albums[] = $album;
        }
        return ($albums);
    }

    public function getPendingAlbumMBIds()
    {
        $mbIds = array();
        $query = '
                SELECT
                    DISTINCT FIT.mb_album_id AS mbid
                FROM FILE_ID3_TAG FIT
                WHERE FIT.mb_album_id IS NOT NULL
                AND NOT EXISTS
                    (SELECT mbid FROM MB_CACHE_ALBUM MCA WHERE MCA.mbid = FIT.mb_album_id)
            ';
        $results = $this->dbh->query($query);
        $totalResults = count($results);
        for ($i = 0; $i < $totalResults; $i++) {
            $mbIds[] = $results[$i]->mbid;
        }
        return ($mbIds);
    }

    public function mbAlbumScrap(string $album = "", string $artist = "", string $year = "")
    {
        $mbAlbum = new \aportela\MusicBrainzWrapper\Release($this->logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON);
        $results = $mbAlbum->search($album, $artist, $year, 1);
        if (count($results) == 1 && !empty($results[0]->mbId)) {
            $whereConditions = array(
                " mb_album_id IS NULL ",
                " artist = :artist ",
                " album = :album "
            );
            if (!empty($year)) {
                " year = :year ";
            }
            $query = " UPDATE FILE_ID3_TAG SET mb_album_id = :mbid WHERE " . implode(" AND ", $whereConditions);
            $results = $this->dbh->exec($query, array(
                new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $results[0]->mbId),
                new \aportela\DatabaseWrapper\Param\StringParam(":artist", $artist),
                new \aportela\DatabaseWrapper\Param\StringParam(":album", $album),
            ));
        }
    }

    public function mbAlbumMBIdScrap(string $mbId = "")
    {
        $mbAlbum = new \aportela\MusicBrainzWrapper\Release($this->logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON);
        $mbAlbum->get($mbId);
        if (!empty($mbAlbum->mbId) && !empty($mbAlbum->title)) {

            $this->dbh->exec(
                "
                    INSERT INTO MB_CACHE_RELEASE (mbid, title, year, artist_mbid, artist_name, track_count, json) VALUES (:mbid, :title, :year, :artist_mbid, :artist_name, :track_count, :json)
                    ON CONFLICT(mbid) DO
                        UPDATE SET title = :title, artist_mbid = :artist_mbid, artist_name = :artist_name, track_count = :track_count, json = :json
                ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $mbAlbum->mbId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":title", $mbAlbum->title),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":year", $mbAlbum->year > 0 ? intval($mbAlbum->year) : 0),
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $mbAlbum->artist->mbId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_name", $mbAlbum->artist->name),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":track_count", $mbAlbum->trackCount > 0 ? intval($mbAlbum->trackCount) : 0),
                    new \aportela\DatabaseWrapper\Param\StringParam(":json", $mbAlbum->raw)
                )
            );
        } else {
            die("NO");
        }
    }

    public function getAllDatabaseFiles(): array
    {
        $results = array();
        $query = " SELECT id, base_path AS path, file_name as filename FROM FILE ORDER BY base_path, file_name ";
        $results = $this->dbh->query($query);
        return ($results);
    }

    public function removeDatabaseReferences(string $fileId)
    {
        $params = array(
            (new \Spieldose\Database\DBParam())->str(":file_id", $fileId)
        );
        $this->dbh->exec(" DELETE FROM STATS WHERE file_id = :file_id ", $params);
        $this->dbh->exec(" DELETE FROM PLAYLIST_TRACK WHERE file_id = :file_id ", $params);
        $this->dbh->exec(" DELETE FROM LOVED_FILE WHERE file_id = :file_id ", $params);
        $this->dbh->exec(" DELETE FROM FILE WHERE id = :file_id ", $params);
    }
}
