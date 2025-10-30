<?php

declare(strict_types=1);

namespace Spieldose\Library\Scraper;

class MusicBrainzArtistScraper
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;
    private \aportela\MusicBrainzWrapper\Artist $musicBrainzArtistAPI;
    private bool $refreshExistingCache = false;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger, ?string $mbCachePath = null, bool $refreshExistingCache = false)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->musicBrainzArtistAPI = new \aportela\MusicBrainzWrapper\Artist($logger, \aportela\MusicBrainzWrapper\APIFormat::JSON, \aportela\MusicBrainzWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, $mbCachePath, $refreshExistingCache);
        $this->refreshExistingCache = $refreshExistingCache;
    }

    public function __destruct() {}

    /**
     * returns all ID3 entries with artist name but without MusicBrainz artist id
     */
    private function getID3OrphanedMBIdArtistNames(bool $randomize = false): array
    {
        $names = [];
        $results = $this->dbh->query(
            sprintf(
                "
                    SELECT
                        DISTINCT FIT.artist AS name
                    FROM FILE_ID3_TAG FIT
                    WHERE
                        FIT.mb_artist_id IS NULL
                    AND
                        FIT.artist IS NOT NULL
                    %s
                ",
                $randomize ? " ORDER BY RANDOM() " : null
            )
        );
        foreach ($results as $result) {
            $names[] = $result->name;
        }
        return ($names);
    }

    /**
     * set ID3 MusicBrainz artist id for artist name with empty artistMBId
     */
    private function setID3OrphanedArtistNameMBId(string $artistName, string $artistMBId)
    {
        $this->dbh->execute(
            "
                UPDATE FILE_ID3_TAG SET
                    mb_artist_id = :mb_artist_id
                WHERE
                    artist = :artist
                AND
                    mb_artist_id IS NULL
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":mb_artist_id", $artistMBId),
                new \aportela\DatabaseWrapper\Param\StringParam(":artist", $artistName),
            ]
        );
    }

    private function getMissingCacheArtistMBIds()
    {
        $mbIds = [];
        $results = $this->dbh->query(
            "
                SELECT
                    FILE_ID3_TAG.mb_artist_id AS mbid
                FROM FILE_ID3_TAG
                LEFT JOIN CACHE_ARTIST_MUSICBRAINZ ON CACHE_ARTIST_MUSICBRAINZ.mbid = FILE_ID3_TAG.mb_artist_id
                WHERE
                    FILE_ID3_TAG.mb_artist_id IS NOT NULL
                AND
                    CACHE_ARTIST_MUSICBRAINZ.mbid IS NULL
                UNION
                SELECT
                    FILE_ID3_TAG.mb_album_artist_id AS mbid
                FROM FILE_ID3_TAG
                LEFT JOIN CACHE_ARTIST_MUSICBRAINZ ON CACHE_ARTIST_MUSICBRAINZ.mbid = FILE_ID3_TAG.mb_album_artist_id
                WHERE
                    FILE_ID3_TAG.mb_album_artist_id IS NOT NULL
                AND
                    CACHE_ARTIST_MUSICBRAINZ.mbid IS NULL
            "
        );
        foreach ($results as $result) {
            $mbIds[] = $result->mbid;
        }
        return ($mbIds);
    }

    private function getAllArtistMBIds()
    {
        $mbIds = [];
        $results = $this->dbh->query(
            "
                SELECT
                    FILE_ID3_TAG.mb_artist_id AS mbid
                FROM FILE_ID3_TAG
                UNION
                SELECT
                    FILE_ID3_TAG.mb_album_artist_id AS mbid
                FROM FILE_ID3_TAG
            "
        );
        foreach ($results as $result) {
            $mbIds[] = $result->mbid;
        }
        return ($mbIds);
    }

    /**
     * save MusicBrainz artist cache (metadata/genres/relationships)
     */
    private function saveMBCacheArtist(\aportela\MusicBrainzWrapper\ParseHelpers\ArtistHelper $artist)
    {
        $this->dbh->execute(
            "
                INSERT INTO CACHE_ARTIST_MUSICBRAINZ
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
                DELETE FROM CACHE_ARTIST_MUSICBRAINZ_GENRE
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
                    INSERT INTO CACHE_ARTIST_MUSICBRAINZ_GENRE
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
                DELETE FROM CACHE_ARTIST_MUSICBRAINZ_URL_RELATIONSHIP
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
                    INSERT INTO CACHE_ARTIST_MUSICBRAINZ_URL_RELATIONSHIP
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
        $scanStartTime = microtime(true);
        $artistNames = $this->getID3OrphanedMBIdArtistNames(true);
        $totalArtistsNames = count($artistNames);
        for ($i = 0; $i < $totalArtistsNames; $i++) {
            if ($scrapItemCallback != null) {
                call_user_func($scrapItemCallback, $artistNames, $totalArtistsNames, $i);
            }
            try {
                $mbDataResults = $artist->search($artistNames[$i], 1);
                if (count($mbDataResults) == 1) {
                    if ($mbDataResults[0]->mbId != \aportela\MusicBrainzWrapper\Artist::NO_ARTIST_MB_ID) {
                        $this->setID3OrphanedArtistNameMBId($artistNames[$i], $mbDataResults[0]->mbId);
                    }
                }
            } catch (\aportela\MusicBrainzWrapper\Exception\NotFoundException $e) {
                $this->logger->notice("MusicBrainz artist name search returns no results", [$artistNames[$i], $e->getMessage()]);
            } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
                $this->logger->warning("MusicBrainz API server (search artist) not reachable", [$artistNames[$i], $e->getMessage()]);
            }
        }
        return (microtime(true) - $scanStartTime);
    }

    public function replaceMbIdRedirect(string $oldMbId, string $newMbId)
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

    public function scrapMissingCache(?callable $scrapItemCallback = null): float
    {
        $scanStartTime = microtime(true);
        $artistMbIds = $this->refreshExistingCache ? $this->getAllArtistMBIds() : $this->getMissingCacheArtistMBIds();
        $totalArtistMbIds = count($artistMbIds);
        for ($i = 0; $i < $totalArtistMbIds; $i++) {
            if ($scrapItemCallback != null) {
                call_user_func($scrapItemCallback, $artistMbIds, $totalArtistMbIds, $i);
            }
            try {
                $artist = $this->musicBrainzArtistAPI->get($artistMbIds[$i]);
                /**
                 * sometimes we have a mbId but MusicBrainz API redirects to another mbId, we must
                 * replace old mbId with new mbId on FILE_ID3_TAG table
                 * https://musicbrainz.org/doc/MusicBrainz_Database/Schema%23Artist#MBID_redirects
                 *
                 */
                if ($artist->mbId != $artistMbIds[$i]) {
                    $this->replaceMbIdRedirect($artistMbIds[$i], $artist->mbId);
                }
                $this->saveMBCacheArtist($artist);
            } catch (\aportela\MusicBrainzWrapper\Exception\NotFoundException $e) {
                $this->logger->warning("MusicBrainz artist id get not found", [$artistMbIds[$i], $e->getMessage()]);
            } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
                $this->logger->warning("MusicBrainz API server (get artist) not reachable", [$artistMbIds[$i], $e->getMessage()]);
            } catch (\Throwable $e) {
                $this->logger->warning("MusicBrainz artist id get error", [$artistMbIds[$i], $e->getMessage(), $e->getPrevious()]);
            }
        }
        return (microtime(true) - $scanStartTime);
    }
}
