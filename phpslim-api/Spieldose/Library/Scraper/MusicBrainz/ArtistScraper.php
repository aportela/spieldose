<?php

declare(strict_types=1);

namespace Spieldose\Library\Scraper\MusicBrainz;

class ArtistScraper
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;
    private \aportela\MusicBrainzWrapper\Artist $musicBrainzArtistAPI;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger, \aportela\SimpleFSCache\Cache $cache)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->musicBrainzArtistAPI = new \aportela\MusicBrainzWrapper\Artist($logger, \aportela\MusicBrainzWrapper\APIFormat::JSON, \aportela\MusicBrainzWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, $cache);
    }

    public function __destruct() {}

    /**
     * returns all ID3 entries with artist name but without MusicBrainz artist id
     */
    private function getID3OrphanedArtistMBIdData(bool $randomize = false): array
    {
        return ($this->dbh->query(
            sprintf(
                "
                    SELECT
                        FILE_ID3_TAG.file_id AS fileId, FILE_ID3_TAG.artist AS artistName
                    FROM FILE_ID3_TAG
                    LEFT JOIN FILE_ID3_TAG_MUSICBRAINZ_ARTIST ON FILE_ID3_TAG_MUSICBRAINZ_ARTIST.file_id = FILE_ID3_TAG.file_id
                    WHERE
                        FILE_ID3_TAG.artist IS NOT NULL
                    AND
                        FILE_ID3_TAG_MUSICBRAINZ_ARTIST.file_id IS NULL
                    %s
                ",
                $randomize ? " ORDER BY RANDOM() " : null
            )
        ));
    }

    /**
     * set ID3 MusicBrainz artist id for artist name on files with missing artist MBId
     */
    private function setID3OrphanedArtistMBIdData(string $fileId, string $artistMBId)
    {
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
                new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $artistMBId),
            ]
        );
    }

    private function getAllArtistMBIds(bool $ignoreCache)
    {
        $allArtistMBIdsQuery = "
                SELECT
                    FILE_ID3_TAG.mb_artist_id AS mbid
                FROM FILE_ID3_TAG
                WHERE
                    FILE_ID3_TAG.mb_artist_id IS NOT NULL
                UNION
                SELECT
                    FILE_ID3_TAG.mb_album_artist_id AS mbid
                FROM FILE_ID3_TAG
                WHERE
                    FILE_ID3_TAG.mb_album_artist_id IS NOT NULL
            ";
        $notCachedArtistMBIdsQuery = "
                SELECT
                    FILE_ID3_TAG.mb_artist_id AS mbid
                FROM FILE_ID3_TAG
                LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG.mb_artist_id
                WHERE
                    FILE_ID3_TAG.mb_artist_id IS NOT NULL
                AND
                    CACHE_MUSICBRAINZ_ARTIST.mbid IS NULL
                UNION
                SELECT
                    FILE_ID3_TAG.mb_album_artist_id AS mbid
                FROM FILE_ID3_TAG
                LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FILE_ID3_TAG.mb_album_artist_id
                WHERE
                    FILE_ID3_TAG.mb_album_artist_id IS NOT NULL
                AND
                    CACHE_MUSICBRAINZ_ARTIST.mbid IS NULL
            ";
        return (array_map(fn($result) => $result->mbid, $this->dbh->query($ignoreCache ? $allArtistMBIdsQuery : $notCachedArtistMBIdsQuery)));
    }

    public function hasCache(string $mbId): bool
    {
        $results = $this->dbh->query(
            "
                SELECT
                    COUNT(mbid) AS total
                FROM CACHE_MUSICBRAINZ_ARTIST
                WHERE mbid = :mbid
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $mbId),
            ]
        );
        return (intval($results[0]->total) === 1);
    }

    /**
     * save MusicBrainz artist cache (metadata/genres/relationships)
     */
    private function saveCache(\aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper $artist)
    {
        $this->dbh->execute(
            "
                INSERT INTO CACHE_MUSICBRAINZ_ARTIST
                    (mbid, name, country, ctime, mtime)
                VALUES
                    (:mbid, :name, :country, :current_timestamp, NULL)
                ON CONFLICT (mbid) DO
                    UPDATE SET
                        name = :name,
                        country = :country,
                        mtime = :current_timestamp
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $artist->mbId),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $artist->name),
                ! empty($artist->country) ?
                    new \aportela\DatabaseWrapper\Param\StringParam(":country", $artist->country)
                    :
                    new \aportela\DatabaseWrapper\Param\NullParam(":country"),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
            ]
        );
        $this->dbh->execute(
            "
                DELETE FROM CACHE_MUSICBRAINZ_ARTIST_GENRE
                WHERE
                    artist_mbid = :artist_mbid
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $artist->mbId)
            ]
        );
        foreach ($artist->genres as $genre) {
            $this->dbh->execute(
                "
                    INSERT INTO CACHE_MUSICBRAINZ_ARTIST_GENRE
                        (artist_mbid, genre)
                    VALUES
                        (:artist_mbid, :genre)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $artist->mbId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":genre", $genre)
                ]
            );
        }
        $this->dbh->execute(
            "
                DELETE FROM CACHE_MUSICBRAINZ_ARTIST_URL_RELATIONSHIP
                WHERE
                    artist_mbid = :artist_mbid
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $artist->mbId)
            ]
        );
        foreach ($artist->relations as $relation) {
            $this->dbh->execute(
                "
                    INSERT INTO CACHE_MUSICBRAINZ_ARTIST_URL_RELATIONSHIP
                        (artist_mbid, relation_type_id, name, url)
                    VALUES
                        (:artist_mbid, :relation_type_id, :name, :url)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $artist->mbId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":relation_type_id", $relation->typeId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":name", $relation->type),
                    new \aportela\DatabaseWrapper\Param\StringParam(":url", $relation->url)
                ]
            );
        }
    }

    /**
     * scrap all id3 artist names without MusicBrainz artist id
     */
    public function scrapArtistsWithoutMusicBrainzId(?callable $scrapItemCallback = null): float
    {
        $scrapStartTime = microtime(true);
        $missingElements = $this->getID3OrphanedArtistMBIdData(false);
        $totalElements = count($missingElements);
        $cachedElements = [];
        for ($i = 0; $i < $totalElements; $i++) {
            if ($scrapItemCallback != null) {
                call_user_func($scrapItemCallback, $missingElements, $totalElements, $i);
            }
            try {
                if (! array_key_exists($missingElements[$i]->artistName, $cachedElements)) {
                    $mbDataResults = $this->musicBrainzArtistAPI->search($missingElements[$i]->artistName, 1);
                    if (count($mbDataResults) == 1) {
                        if ($mbDataResults[0]->mbId != \aportela\MusicBrainzWrapper\Artist::NO_ARTIST_MB_ID) {
                            $cachedElements[$missingElements[$i]->artistName] = $mbDataResults[0]->mbId;
                            $this->setID3OrphanedArtistMBIdData($missingElements[$i]->fileId, $mbDataResults[0]->mbId);
                        }
                    }
                } else {
                    $this->setID3OrphanedArtistMBIdData($missingElements[$i]->fileId, $cachedElements[$missingElements[$i]->artistName]);
                }
            } catch (\aportela\MusicBrainzWrapper\Exception\NotFoundException $e) {
                $this->logger->notice("MusicBrainz artist name search returns no results", [$missingElements[$i], $e->getMessage()]);
            } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
                $this->logger->warning("MusicBrainz API server (search artist) not reachable", [$missingElements[$i], $e->getMessage()]);
            }
        }
        return (microtime(true) - $scrapStartTime);
    }

    private function replaceMbIdRedirect(string $oldMbId, string $newMbId)
    {
        $this->dbh->execute(
            "
                UPDATE FILE_ID3_TAG
                SET
                    mb_artist_id = :new_mbid
                WHERE
                    mb_artist_id = :old_mbid
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":new_mbid", $newMbId),
                new \aportela\DatabaseWrapper\Param\StringParam(":old_mbid", $oldMbId)
            ]
        );

        $this->dbh->execute(
            "
                UPDATE FILE_ID3_TAG
                SET
                    mb_album_artist_id = :new_mbid
                WHERE
                    mb_album_artist_id = :old_mbid
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":new_mbid", $newMbId),
                new \aportela\DatabaseWrapper\Param\StringParam(":old_mbid", $oldMbId)
            ]
        );
    }

    public function scrap(string $mbId): bool
    {
        try {
            $artist = $this->musicBrainzArtistAPI->get($mbId);
            /**
             * sometimes we have a mbId but MusicBrainz API redirects to another mbId, we must
             * replace old mbId with new mbId on FILE_ID3_TAG table
             * https://musicbrainz.org/doc/MusicBrainz_Database/Schema%23Artist#MBID_redirects
             *
             */
            if ($artist->mbId != $mbId) {
                $this->replaceMbIdRedirect($mbId, $artist->mbId);
            }
            $this->saveCache($artist);
            return (true);
        } catch (\aportela\MusicBrainzWrapper\Exception\NotFoundException $e) {
            $this->logger->warning("MusicBrainz artist id get not found", [$mbId, $e->getMessage()]);
        } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
            $this->logger->warning("MusicBrainz API server (get artist) not reachable", [$mbId, $e->getMessage()]);
        } catch (\Throwable $e) {
            $this->logger->warning("MusicBrainz artist id get error", [$mbId, $e->getMessage(), $e->getPrevious()]);
        }
        return (false);
    }

    public function scrapMissingCache(?callable $scrapItemCallback = null, bool $force = false): float
    {
        $scrapStartTime = microtime(true);
        $artistMbIds = $this->getAllArtistMBIds($force);
        $totalArtistMbIds = count($artistMbIds);
        for ($i = 0; $i < $totalArtistMbIds; $i++) {
            if ($scrapItemCallback != null) {
                call_user_func($scrapItemCallback, $artistMbIds, $totalArtistMbIds, $i);
            }
            $this->scrap($artistMbIds[$i]);
        }
        return (microtime(true) - $scrapStartTime);
    }
}
