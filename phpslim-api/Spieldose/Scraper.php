<?php

declare(strict_types=1);

namespace Spieldose;

class Scraper
{

    private $dbh = null;
    private $logger;
    private $apiFormat = null;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger, string $apiFormat = \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->apiFormat = $apiFormat;
    }

    public function __destruct()
    {
    }

    public function getArtistNamesWithoutMusicBrainzId(): array
    {
        $artistNames = array();
        $query = " SELECT DISTINCT artist FROM FILE_ID3_TAG WHERE mb_artist_id IS NULL AND artist IS NOT NULL ORDER BY RANDOM() ";
        $results = $this->dbh->query($query);
        $totalArtistNames = count($results);
        for ($i = 0; $i < $totalArtistNames; $i++) {
            $artistNames[] = $results[$i]->artist;
        }
        return ($artistNames);
    }

    public function searchArtistMusicBrainzIdByName(string $artistName): ?string
    {
        $mbId = null;
        $mbArtist = new \aportela\MusicBrainzWrapper\Artist($this->logger, $this->apiFormat);
        try {
            $results = $mbArtist->search($artistName, 1);
        } catch (\aportela\MusicBrainzWrapper\Exception\NotFoundException $e) {
        }
        if (count($results) == 1 && !empty($results[0]->mbId)) {
            $mbId = $results[0]->mbId;
        }
        return ($mbId);
    }

    public function saveArtistMusicBrainzId(string $mbId, string $artistName): void
    {
        $query = " UPDATE FILE_ID3_TAG SET mb_artist_id = :mbid WHERE mb_artist_id IS NULL AND artist = :artist ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $mbId),
            new \aportela\DatabaseWrapper\Param\StringParam(":artist", $artistName),
        );
        $this->dbh->exec($query, $params);
    }

    public function getArtistMusicBrainzIdsWithoutCachedMetadata(): array
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

    public function getArtistMusicBrainzMetadata($mbId): \aportela\MusicBrainzWrapper\Artist
    {
        $mbArtist = new \aportela\MusicBrainzWrapper\Artist($this->logger, $this->apiFormat);
        $mbArtist->get($mbId);
        return ($mbArtist);
    }

    public function saveArtistMusicBrainzCachedMetadata(\aportela\MusicBrainzWrapper\Artist $mbArtist): void
    {
        $query = "
            INSERT INTO MB_CACHE_ARTIST (mbid, name, image, json) VALUES (:mbid, :name, :image, :json)
                ON CONFLICT(mbid) DO
            UPDATE SET name = :name, image = :image, json = :json
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $mbArtist->mbId),
            new \aportela\DatabaseWrapper\Param\StringParam(":name", $mbArtist->name),
            new \aportela\DatabaseWrapper\Param\NullParam(":image"),
            new \aportela\DatabaseWrapper\Param\StringParam(":json", $mbArtist->raw)
        );
        $this->dbh->exec($query, $params);
    }


    public function getAlbumsWithoutMusicBrainzId(): array
    {
        $albums = array();
        $query = "
            SELECT DISTINCT COALESCE(album_artist, artist) AS artist, album, year
            FROM FILE_ID3_TAG
            WHERE mb_album_id IS NULL
            AND (artist IS NOT NULL OR album_artist IS NOT NULL)
            AND album IS NOT NULL
            ORDER BY album
        ";
        $results = $this->dbh->query($query);
        $totalAlbums = count($results);
        for ($i = 0; $i < $totalAlbums; $i++) {
            $album = new \stdClass();
            $album->artist = $results[$i]->artist;
            $album->album = $results[$i]->album;
            $album->year = $results[$i]->year;
            $albums[] = $album;
        }
        return ($albums);
    }

    public function searchAlbumMusicBrainzId(string $album, string $artist, string $year = ""): ?string
    {
        $mbId = null;
        $mbAlbum = new \aportela\MusicBrainzWrapper\Release($this->logger, \aportela\MusicBrainzWrapper\Entity::API_FORMAT_JSON);
        try {
            $results = $mbAlbum->search($album, $artist, $year, 1);
            if (count($results) == 1 && !empty($results[0]->mbId)) {
                $mbId = $results[0]->mbId;
            }
        } catch (\aportela\MusicBrainzWrapper\Exception\NotFoundException $e) {
        }
        return ($mbId);
    }

    public function saveAlbumMusicBrainzId(string $mbId, string $album, string $artist, string $year = ""): void
    {
        $whereConditions = array(
            " mb_album_id IS NULL ",
            " artist = :artist ",
            " album = :album "
        );
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $mbId),
            new \aportela\DatabaseWrapper\Param\StringParam(":artist", $artist),
            new \aportela\DatabaseWrapper\Param\StringParam(":album", $album),
        );
        if (!empty($year)) {
            $whereConditions[] = " year = :year ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":year", $year);
        }

        $query = " UPDATE FILE_ID3_TAG SET mb_album_id = :mbid WHERE " . implode(" AND ", $whereConditions);
        $this->dbh->exec($query, $params);
    }

    public function getAlbumMusicBrainzIdsWithoutCachedMetadata(): array
    {
        $mbIds = array();
        $query = '
            SELECT
                DISTINCT FIT.mb_album_id AS mbid
            FROM FILE_ID3_TAG FIT
            WHERE FIT.mb_album_id IS NOT NULL
            AND NOT EXISTS (SELECT mbid FROM MB_CACHE_RELEASE MCR WHERE MCR.mbid = FIT.mb_album_id)
        ';
        $results = $this->dbh->query($query);
        $totalResults = count($results);
        for ($i = 0; $i < $totalResults; $i++) {
            $mbIds[] = $results[$i]->mbid;
        }
        return ($mbIds);
    }

    public function getAlbumMusicBrainzMetadata($mbId): \aportela\MusicBrainzWrapper\Release
    {
        $mbAlbum = new \aportela\MusicBrainzWrapper\Release($this->logger, $this->apiFormat);
        $mbAlbum->get($mbId);
        return ($mbAlbum);
    }

    public function saveAlbumMusicBrainzCachedMetadata(\aportela\MusicBrainzWrapper\Release $mbAlbum): void
    {
        $query = "
            INSERT INTO MB_CACHE_RELEASE (mbid, title, year, artist_mbid, artist_name, track_count, json) VALUES (:mbid, :title, :year, :artist_mbid, :artist_name, :track_count, :json)
                ON CONFLICT(mbid) DO
            UPDATE SET title = :title, artist_mbid = :artist_mbid, artist_name = :artist_name, track_count = :track_count, json = :json
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $mbAlbum->mbId),
            new \aportela\DatabaseWrapper\Param\StringParam(":title", $mbAlbum->title),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":year", $mbAlbum->year > 0 ? intval($mbAlbum->year) : 0),
            new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $mbAlbum->artist->mbId),
            new \aportela\DatabaseWrapper\Param\StringParam(":artist_name", $mbAlbum->artist->name),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":track_count", $mbAlbum->trackCount > 0 ? intval($mbAlbum->trackCount) : 0),
            new \aportela\DatabaseWrapper\Param\StringParam(":json", $mbAlbum->raw)
        );
        $this->dbh->exec($query, $params);
    }
}
