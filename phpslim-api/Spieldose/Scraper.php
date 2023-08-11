<?php

declare(strict_types=1);

namespace Spieldose;

class Scraper
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;
    private \aportela\MusicBrainzWrapper\APIFormat $apiFormat;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger, \aportela\MusicBrainzWrapper\APIFormat $apiFormat)
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

    public function getArtistMusicBrainzIds(): array
    {
        $mbIds = array();
        $query = '
                SELECT
                    DISTINCT FIT.mb_artist_id AS mbid
                FROM FILE_ID3_TAG FIT
                WHERE FIT.mb_artist_id IS NOT NULL

                UNION

                SELECT
                    DISTINCT FIT.mb_album_artist_id AS mbid
                FROM FILE_ID3_TAG FIT
                WHERE FIT.mb_album_artist_id IS NOT NULL
            ';
        $results = $this->dbh->query($query);
        $totalResults = count($results);
        for ($i = 0; $i < $totalResults; $i++) {
            $mbIds[] = $results[$i]->mbid;
        }
        return ($mbIds);
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
            INSERT INTO MB_CACHE_ARTIST (mbid, name, country, image, json) VALUES (:mbid, :name, :country, :image, :json)
                ON CONFLICT(mbid) DO
            UPDATE SET name = :name, country = :country, image = :image, json = :json
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $mbArtist->mbId),
            new \aportela\DatabaseWrapper\Param\StringParam(":name", $mbArtist->name),
            new \aportela\DatabaseWrapper\Param\NullParam(":image"),
            new \aportela\DatabaseWrapper\Param\StringParam(":json", $mbArtist->raw)
        );
        if (!empty($mbArtist->country)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":country", $mbArtist->country);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":country");
        }
        $this->dbh->exec($query, $params);
        $query = "
            DELETE FROM MB_CACHE_ARTIST_URL_RELATIONSHIP WHERE artist_mbid = :artist_mbid
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $mbArtist->mbId)
        );
        $this->dbh->exec($query, $params);
        $allowedRelations = array_column(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType::cases(), 'value');
        if (is_array($mbArtist->relations) && count($mbArtist->relations) > 0) {
            foreach ($mbArtist->relations as $relation) {
                if (in_array($relation->typeId, $allowedRelations)) {
                    $query = "
                        INSERT INTO MB_CACHE_ARTIST_URL_RELATIONSHIP (artist_mbid, url_relationship_typeid, url_relationship_value) VALUES (:artist_mbid, :url_relationship_typeid, :url_relationship_value)
                            ON CONFLICT(artist_mbid, url_relationship_typeid, url_relationship_value) DO
                        UPDATE SET url_relationship_value = :url_relationship_value
                    ";
                    $params = array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $mbArtist->mbId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":url_relationship_typeid", $relation->typeId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":url_relationship_value", $relation->url)
                    );
                    $this->dbh->exec($query, $params);
                }
            }
        }
        $this->dbh->exec($query, $params);
        $query = "
            DELETE FROM MB_CACHE_ARTIST_GENRE WHERE artist_mbid = :artist_mbid
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $mbArtist->mbId)
        );
        $this->dbh->exec($query, $params);
        if (is_array($mbArtist->genres) && count($mbArtist->genres) > 0) {
            foreach ($mbArtist->genres as $genre) {
                $query = "
                    INSERT INTO MB_CACHE_ARTIST_GENRE (artist_mbid, genre) VALUES (:artist_mbid, :genre)
                ";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $mbArtist->mbId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":genre", $genre)
                );
                $this->dbh->exec($query, $params);
            }
        }
    }

    public function saveArtistWikipediaCachedMetadata(string $mbArtistId, string $html): void
    {
        $query = "
            INSERT INTO MB_WIKIPEDIA_CACHE_ARTIST (artist_mbid, language, html) VALUES (:artist_mbid, :language, :html)
                ON CONFLICT(`artist_mbid`, `language`) DO
            UPDATE SET html = :html
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $mbArtistId),
            new \aportela\DatabaseWrapper\Param\StringParam(":language", \aportela\MediaWikiWrapper\Language::ENGLISH->value),
            new \aportela\DatabaseWrapper\Param\StringParam(":html", $html)
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

    public function getAlbumMusicBrainzIds(): array
    {
        $mbIds = array();
        $query = '
            SELECT
                DISTINCT FIT.mb_album_id AS mbid
            FROM FILE_ID3_TAG FIT
            WHERE FIT.mb_album_id IS NOT NULL
        ';
        $results = $this->dbh->query($query);
        $totalResults = count($results);
        for ($i = 0; $i < $totalResults; $i++) {
            $mbIds[] = $results[$i]->mbid;
        }
        return ($mbIds);
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
            new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $mbAlbum->artist->mbId),
            new \aportela\DatabaseWrapper\Param\StringParam(":artist_name", $mbAlbum->artist->name),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":track_count", $mbAlbum->trackCount > 0 ? intval($mbAlbum->trackCount) : 0),
            new \aportela\DatabaseWrapper\Param\StringParam(":json", $mbAlbum->raw)
        );
        if (isset($mbAlbum->year) && $mbAlbum->year > 0) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":year", $mbAlbum->year);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":year");
        }
        $this->dbh->exec($query, $params);
        $query = "
            DELETE FROM MB_CACHE_RELEASE_TRACK WHERE release_mbid = :release_mbid
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":release_mbid", $mbAlbum->mbId)
        );
        $this->dbh->exec($query, $params);
        if (is_array($mbAlbum->tracks) && count($mbAlbum->tracks) > 0) {
            foreach ($mbAlbum->tracks as $track) {
                $query = "
                    INSERT INTO MB_CACHE_RELEASE_TRACK (release_mbid, track_mbid, title, artist_mbid, artist_name, track_number) VALUES (:release_mbid, :track_mbid, :title, :artist_mbid, :artist_name, :track_number)
                        ON CONFLICT(track_mbid) DO
                    UPDATE SET release_mbid = :release_mbid, title = :title, artist_mbid = :artist_mbid, artist_name = :artist_name, track_number = :track_number
                ";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":release_mbid", $mbAlbum->mbId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":track_mbid", $track->mbId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":title", $track->title),
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $track->artist->mbId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_name", $track->artist->name),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":track_number", $track->number)
                );
                $this->dbh->exec($query, $params);
            }
        }
    }
}
