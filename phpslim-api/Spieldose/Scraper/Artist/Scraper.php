<?php

declare(strict_types=1);

namespace Spieldose\Scraper\Artist;

class Scraper
{
    public static function getArtistNamesWithoutMusicBrainzId(\aportela\DatabaseWrapper\DB $db, bool $randomize = false): array
    {
        $names = [];
        $results = $db->query(
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

    public static function getMusicBrainzArtistsWithoutCache(\aportela\DatabaseWrapper\DB $db, bool $randomize = false): array
    {
        $artists = [];
        $query = $randomize ? "
            SELECT
                mbid, name
            FROM (
                SELECT
                    FIT.mb_artist_id AS mbid, FIT.artist AS name
                FROM FILE_ID3_TAG FIT
                WHERE FIT.mb_artist_id IS NOT NULL
                AND NOT EXISTS
                    (SELECT CAM.mbid FROM CACHE_MUSICBRAINZ_ARTIST CAM WHERE CAM.mbid = FIT.mb_artist_id)

                UNION

                SELECT
                    FIT.mb_album_artist_id AS mbid, FIT.album_artist AS name
                FROM FILE_ID3_TAG FIT
                WHERE FIT.mb_album_artist_id IS NOT NULL
                AND NOT EXISTS
                    (SELECT CAM.mbid FROM CACHE_MUSICBRAINZ_ARTIST CAM WHERE CAM.mbid = FIT.mb_album_artist_id)
            ) TMP
            ORDER BY RANDOM()
        " : "
            SELECT
                FIT.mb_artist_id AS mbid, FIT.artist AS name
            FROM FILE_ID3_TAG FIT
            WHERE FIT.mb_artist_id IS NOT NULL
            AND NOT EXISTS
                (SELECT CAM.mbid FROM CACHE_MUSICBRAINZ_ARTIST CAM WHERE CAM.mbid = FIT.mb_artist_id)

            UNION

            SELECT
                FIT.mb_album_artist_id AS mbid, FIT.album_artist AS name
            FROM FILE_ID3_TAG FIT
            WHERE FIT.mb_album_artist_id IS NOT NULL
            AND NOT EXISTS
                (SELECT CAM.mbid FROM CACHE_MUSICBRAINZ_ARTIST CAM WHERE CAM.mbid = FIT.mb_album_artist_id)
        ";
        $results = $db->query($query);
        foreach ($results as $result) {
            $artists[] = (object) [
                "mbId" => $result->mbid,
                "name" => $result->name,
            ];
        }

        return ($artists);
    }

    public static function getArtistsWithoutLastFMCache(\aportela\DatabaseWrapper\DB $db, bool $randomize = false): array
    {
        $artists = [];
        $query = sprintf(
            "
                SELECT mbid, name
                FROM (
                    SELECT DISTINCT COALESCE(CACHE_MUSICBRAINZ_ARTIST.name, FIT.artist) AS name, FIT.mb_artist_id AS mbid
                    FROM FILE_ID3_TAG FIT
                    LEFT JOIN CACHE_MUSICBRAINZ_ARTIST ON CACHE_MUSICBRAINZ_ARTIST.mbid = FIT.mb_artist_id
                    WHERE FIT.artist IS NOT NULL OR FIT.mb_artist_id IS NOT NULL
                ) TMP_ARTISTS
                WHERE NOT EXISTS
                    (SELECT CAL.name FROM CACHE_ARTIST_LASTFM CAL WHERE CAL.name = TMP_ARTISTS.name OR (CAL.mbid IS NOT NULL AND CAL.mbid = TMP_ARTISTS.mbid))
                %s
            ",
            $randomize ? " ORDER BY RANDOM() " : null
        );
        $results = $db->query($query);
        foreach ($results as $result) {
            $artists[] = (object) [
                "mbId" => $result->mbid,
                "name" => $result->name,
            ];
        }

        return ($artists);
    }

    public static function getMusicBrainzArtistsWithoutWikipediaCache(\aportela\DatabaseWrapper\DB $db, bool $randomize = false): array
    {
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":wikipedia_relation_type_id", \aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_WIKIPEDIA->value),
            new \aportela\DatabaseWrapper\Param\StringParam(":wikidata_relation_type_id", \aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_WIKIDATA->value),
        ];
        $artists = [];
        $query = sprintf(
            "
                SELECT DISTINCT CAM.mbid, CAM.name
                FROM CACHE_MUSICBRAINZ_ARTIST CAM
                LEFT JOIN CACHE_MUSICBRAINZ_ARTIST_URL_RELATIONSHIP CAMUR1 ON CAMUR1.artist_mbid = CAM.mbid AND CAMUR1.relation_type_id = :wikipedia_relation_type_id
                LEFT JOIN CACHE_MUSICBRAINZ_ARTIST_URL_RELATIONSHIP CAMUR2 ON CAMUR2.artist_mbid = CAM.mbid AND CAMUR2.relation_type_id = :wikidata_relation_type_id
                WHERE NOT EXISTS
                    (SELECT CAW.mbid FROM CACHE_ARTIST_WIKIPEDIA CAW WHERE CAW.mbid = CAM.mbid)
                AND (
                    CAMUR1.url IS NOT NULL
                    OR
                    CAMUR2.url IS NOT NULL
                )
                %s
            ",
            $randomize ? " ORDER BY RANDOM() " : null
        );
        $results = $db->query($query, $params);
        foreach ($results as $result) {
            $artists[] = (object) [
                "mbId" => $result->mbid,
                "name" => $result->name,
            ];
        }

        return ($artists);
    }

    public static function scrapMusicBrainz(\Psr\Log\LoggerInterface $logger, \aportela\DatabaseWrapper\DB $db, ?string $mbId, ?string $name): bool
    {
        $success = false;
        $artist = (object) [
            "mbId" => $mbId ?? null,
            "name" => $name ?? null,
        ];
        try {
            $musicBrainz = new \Spieldose\Scraper\Artist\MusicBrainz($logger, \aportela\MusicBrainzWrapper\APIFormat::JSON);
            // TODO: ignore if we have cache
            if ($musicBrainz->scrap($artist->name, $artist->mbId)) {
                $db->beginTransaction();
                $musicBrainz->fixTags($db);
                $musicBrainz->saveCache($db);
                $artist->mbId = $musicBrainz->mbId;
                $artist->name = $musicBrainz->name;
                $success = true;
                return (true);
            } else {
                $logger->warning(sprintf("[MusicBrainz] artist %s (%s) not scraped", $artist->name, $artist->mbId));
                return (false);
            }
        } catch (\Throwable $throwable) {
            $logger->error(sprintf("[MusicBrainz] error scrapping artist %s (%s): %s", $artist->name, $artist->mbId, $throwable->getMessage()));
            return (false);
        } finally {
            if ($db->inTransaction()) {
                if ($success) {
                    $db->commit();
                } else {
                    $db->rollBack();
                }
            }
        }
    }

    public static function scrapLastFM(\Psr\Log\LoggerInterface $logger, \aportela\DatabaseWrapper\DB $db, string $lastFMAPIKey, ?string $mbId, ?string $name): void
    {
        $success = false;
        $artist = (object) [
            "mbId" => $mbId ?? null,
            "name" => $name ?? null,
        ];
        try {
            $lastFM = new \Spieldose\Scraper\Artist\LastFM($logger, \aportela\LastFMWrapper\APIFormat::JSON, $lastFMAPIKey);
            if ($lastFM->scrap($artist->name, $artist->mbId)) {
                $db->beginTransaction();
                $lastFM->saveCache($db);
                $success = true;
            } else {
                $logger->warning(sprintf("[LastFM] artist %s (%s) not scraped", $artist->name, $artist->mbId));
            }
        } catch (\Throwable $throwable) {
            $logger->error(sprintf("[LastFM] error scraping artist %s (%s): %s", $artist->name, $artist->mbId, $throwable->getMessage()));
        } finally {
            if ($db->inTransaction()) {
                if ($success) {
                    $db->commit();
                } else {
                    $db->rollBack();
                }
            }
        }
    }

    private static function scrapWikipedia(\Psr\Log\LoggerInterface $logger, \aportela\DatabaseWrapper\DB $db, string $mbId): bool
    {
        $success = false;
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $mbId),
            new \aportela\DatabaseWrapper\Param\StringParam(":wikipedia_relation_type_id", \aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_WIKIPEDIA->value),
        ];
        $query = "
            SELECT CAM.mbid, CAM.name, CAMUR.url
            FROM CACHE_MUSICBRAINZ_ARTIST CAM
            INNER JOIN CACHE_MUSICBRAINZ_ARTIST_URL_RELATIONSHIP CAMUR ON CAMUR.artist_mbid = CAM.mbid AND CAMUR.relation_type_id = :wikipedia_relation_type_id
            WHERE CAM.mbid = :mbid
            LIMIT 1
        ";
        $results = $db->query($query, $params);
        if (count($results) === 1) {
            try {
                $wikipedia = new \Spieldose\Scraper\Artist\Wikipedia($logger);
                if ($wikipedia->scrapWikipedia($results[0]->url)) {
                    $db->beginTransaction();
                    $wikipedia->saveCache($db);
                    $success = true;
                    return (true);
                } else {
                    return (false);
                }
            } catch (\Throwable $e) {
                $logger->error(sprintf("[Wikipedia] error scrapping artist %s (%s) url %s: %s", $results[0]->name, $results[0]->mbid, $results[0]->url, $e->getMessage()));
                return (false);
            } finally {
                if ($db->inTransaction()) {
                    if ($success) {
                        $db->commit();
                    } else {
                        $db->rollBack();
                    }
                }
            }
        } else {
            return (false);
        }
    }

    private static function scrapWikidata(\Psr\Log\LoggerInterface $logger, \aportela\DatabaseWrapper\DB $db, string $mbId): bool
    {
        $success = false;
        $params = [
            new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $mbId),
            new \aportela\DatabaseWrapper\Param\StringParam(":wikidata_relation_type_id", \aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_WIKIDATA->value),
        ];
        $query = "
            SELECT CAM.mbid, CAM.name, CAMUR.url
            FROM CACHE_MUSICBRAINZ_ARTIST CAM
            INNER JOIN CACHE_MUSICBRAINZ_ARTIST_URL_RELATIONSHIP CAMUR ON CAMUR.artist_mbid = CAM.mbid AND CAMUR.relation_type_id = :wikidata_relation_type_id
            WHERE CAM.mbid = :mbid
            LIMIT 1
        ";
        $results = $db->query($query, $params);
        if (count($results) === 1) {
            try {
                $wikipedia = new \Spieldose\Scraper\Artist\Wikipedia($logger);
                if ($wikipedia->scrapWikidata($results[0]->url)) {
                    $db->beginTransaction();
                    $wikipedia->mbId = $results[0]->mbid;
                    $wikipedia->saveCache($db);
                    $success = true;
                    return (true);
                } else {
                    return (false);
                }
            } catch (\Throwable $e) {
                $logger->error(sprintf("[Wikidata] error scrapping artist %s (%s) url %s: %s", $results[0]->name, $results[0]->mbid, $results[0]->url, $e->getMessage()));
                return (false);
            } finally {
                if ($db->inTransaction()) {
                    if ($success) {
                        $db->commit();
                    } else {
                        $db->rollBack();
                    }
                }
            }
        } else {
            return (false);
        }
    }

    public static function scrapWiki(\Psr\Log\LoggerInterface $logger, \aportela\DatabaseWrapper\DB $db, string $mbId): bool
    {
        return (self::scrapWikipedia($logger, $db, $mbId) || self::scrapWikidata($logger, $db, $mbId));
    }
}
