<?php

declare(strict_types=1);

namespace Spieldose\Scraper\Artist;

class Scraper
{
    public static function getArtistNamesWithoutMusicBrainzId(\aportela\DatabaseWrapper\DB $dbh, bool $randomize = false): array
    {
        $names = [];
        $query = sprintf(
            "
                SELECT DISTINCT FIT.artist AS name
                FROM FILE_ID3_TAG FIT
                WHERE FIT.mb_artist_id IS NULL
                AND FIT.artist IS NOT NULL
                %s
            ",
            $randomize ? " ORDER BY RANDOM() " : null
        );
        $results = $dbh->query($query);
        foreach ($results as $result) {
            $names[] = $result->name;
        }
        return ($names);
    }

    public static function getMusicBrainzArtistMbIdsWithoutCache(\aportela\DatabaseWrapper\DB $dbh, bool $randomize = false): array
    {
        $mbIds = array();
        $query = !$randomize ? "
            SELECT
                FIT.mb_artist_id AS mbid
            FROM FILE_ID3_TAG FIT
            WHERE FIT.mb_artist_id IS NOT NULL
            AND NOT EXISTS
                (SELECT CAM.mbid FROM CACHE_ARTIST_MUSICBRAINZ CAM WHERE CAM.mbid = FIT.mb_artist_id)

            UNION

            SELECT
                FIT.mb_album_artist_id AS mbid
            FROM FILE_ID3_TAG FIT
            WHERE FIT.mb_album_artist_id IS NOT NULL
            AND NOT EXISTS
                (SELECT CAM.mbid FROM CACHE_ARTIST_MUSICBRAINZ CAM WHERE CAM.mbid = FIT.mb_album_artist_id)
        " : "
            SELECT
                mbid
            FROM (
                SELECT
                    FIT.mb_artist_id AS mbid
                FROM FILE_ID3_TAG FIT
                WHERE FIT.mb_artist_id IS NOT NULL
                AND NOT EXISTS
                    (SELECT CAM.mbid FROM CACHE_ARTIST_MUSICBRAINZ CAM WHERE CAM.mbid = FIT.mb_artist_id)

                UNION

                SELECT
                    FIT.mb_album_artist_id AS mbid
                FROM FILE_ID3_TAG FIT
                WHERE FIT.mb_album_artist_id IS NOT NULL
                AND NOT EXISTS
                    (SELECT CAM.mbid FROM CACHE_ARTIST_MUSICBRAINZ CAM WHERE CAM.mbid = FIT.mb_album_artist_id)
            ) TMP
            ORDER BY RANDOM()
        ";
        $results = $dbh->query($query);
        foreach ($results as $result) {
            $mbIds[] = $result->mbid;
        }
        return ($mbIds);
    }

    public static function getArtistsWithoutLastFMCache(\aportela\DatabaseWrapper\DB $dbh, bool $randomize = false): array
    {
        $artists = array();
        $query = sprintf(
            "
                SELECT mbid, name
                FROM (
                    SELECT DISTINCT COALESCE(CACHE_ARTIST_MUSICBRAINZ.name, FIT.artist) AS name, FIT.mb_artist_id AS mbid
                    FROM FILE_ID3_TAG FIT
                    LEFT JOIN CACHE_ARTIST_MUSICBRAINZ ON CACHE_ARTIST_MUSICBRAINZ.mbid = FIT.mb_artist_id
                    WHERE FIT.artist IS NOT NULL OR FIT.mb_artist_id IS NOT NULL
                ) TMP_ARTISTS
                WHERE NOT EXISTS
                    (SELECT CAL.name FROM CACHE_ARTIST_LASTFM CAL WHERE CAL.name = TMP_ARTISTS.name OR (CAL.mbid IS NOT NULL AND CAL.mbid = TMP_ARTISTS.mbid))
                %s
            ",
            $randomize ? " ORDER BY RANDOM() " : null
        );
        $results = $dbh->query($query);
        foreach ($results as $result) {
            $artists[] = (object) [
                "mbId" => $result->mbid,
                "name" => $result->name
            ];
        }
        return ($artists);
    }


    public static function getMusicBrainzArtistsWithoutWikipediaCache(\aportela\DatabaseWrapper\DB $dbh, bool $randomize = false): array
    {
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":wikipedia_relation_type_id", \aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_WIKIPEDIA->value),
            new \aportela\DatabaseWrapper\Param\StringParam(":wikidata_relation_type_id", \aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_WIKIDATA->value)
        );
        $mbIds = array();
        $query = sprintf(
            "
                SELECT DISTINCT CAM.mbid
                FROM CACHE_ARTIST_MUSICBRAINZ CAM
                LEFT JOIN CACHE_ARTIST_MUSICBRAINZ_URL_RELATIONSHIP CAMUR1 ON CAMUR1.artist_mbid = CAM.mbid AND CAMUR1.relation_type_id = :wikipedia_relation_type_id
                LEFT JOIN CACHE_ARTIST_MUSICBRAINZ_URL_RELATIONSHIP CAMUR2 ON CAMUR2.artist_mbid = CAM.mbid AND CAMUR2.relation_type_id = :wikidata_relation_type_id
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
        $results = $dbh->query($query, $params);
        foreach ($results as $result) {
            $mbIds[] = $result->mbid;
        }
        return ($mbIds);
    }

    public static function scrapMusicBrainz(\Psr\Log\LoggerInterface $logger, \aportela\DatabaseWrapper\DB $dbh, ?string $mbId, ?string $name): void
    {
        $success = false;
        $artist = (object) [
            "mbId" => $mbId ?? null,
            "name" => $name ?? null
        ];
        try {
            $musicBrainzArtist = new \Spieldose\Scraper\Artist\MusicBrainz($logger, \aportela\MusicBrainzWrapper\APIFormat::JSON);
            // TODO: ignore if we have cache
            if ($musicBrainzArtist->scrap($artist->name, $artist->mbId)) {
                $dbh->beginTransaction();
                $musicBrainzArtist->fixTags($dbh);
                $musicBrainzArtist->saveCache($dbh);
                $artist->mbId = $musicBrainzArtist->mbId;
                $artist->name = $musicBrainzArtist->name;
                $success = true;
            } else {
                $logger->warning(sprintf("[MusicBrainz] artist %s (%s) not scraped", $artist->name, $artist->mbId));
            }
        } catch (\Throwable $e) {
            $logger->error(sprintf("[MusicBrainz] error scrapping artist %s (%s): %s", $artist->name, $artist->mbId, $e->getMessage()));
        } finally {
            if ($success) {
                $dbh->commit();
            } else {
                $dbh->rollBack();
            }
        }
    }

    public static function scrapLastFM(\Psr\Log\LoggerInterface $logger, \aportela\DatabaseWrapper\DB $dbh, string $lastFMAPIKey, ?string $mbId, ?string $name): void
    {
        $success = false;
        $artist = (object) [
            "mbId" => $mbId ?? null,
            "name" => $name ?? null
        ];
        // musicbrainz block
        try {
            $musicBrainzArtist = new \Spieldose\Scraper\Artist\MusicBrainz($logger, \aportela\MusicBrainzWrapper\APIFormat::JSON);
            // TODO: ignore if we have cache
            if ($musicBrainzArtist->scrap($artist->name, $artist->mbId)) {
                $dbh->beginTransaction();
                $musicBrainzArtist->fixTags($dbh);
                $musicBrainzArtist->saveCache($dbh);
                $artist->mbId = $musicBrainzArtist->mbId;
                $artist->name = $musicBrainzArtist->name;
                // parse wikipedia url from relations
                foreach ($musicBrainzArtist->relations as $relation) {
                    if ($relation->typeId == \aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_WIKIPEDIA->value) {
                        $artist->wikipediaURL = $relation->url;
                    }
                }
                // parse wikidata url from relations
                if (empty($artist->wikipediaURL)) {
                    foreach ($musicBrainzArtist->relations as $relation) {
                        if ($relation->typeId == \aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_WIKIDATA->value) {
                            $artist->wikidataURL = $relation->url;
                        }
                    }
                }
                $success = true;
            } else {
                $logger->warning(sprintf("[MusicBrainz] artist %s (%s) not scraped", $artist->name, $artist->mbId));
            }
        } catch (\Throwable $e) {
            $logger->error(sprintf("[MusicBrainz] error scrapping artist %s (%s): %s", $artist->name, $artist->mbId, $e->getMessage()));
        } finally {
            if ($success) {
                $dbh->commit();
            } else {
                $dbh->rollBack();
            }
        }
        $success = false;
        // last.fm block
        try {
            $lastFMArtist = new \Spieldose\Scraper\Artist\LastFM($logger, \aportela\LastFMWrapper\APIFormat::JSON, $lastFMAPIKey);
            if ($lastFMArtist->scrap($artist->name, $artist->mbId)) {
                $dbh->beginTransaction();
                $lastFMArtist->saveCache($dbh);
                $success = true;
            } else {
                $logger->warning(sprintf("[LastFM] artist %s (%s) not scraped", $artist->name, $artist->mbId));
            }
        } catch (\Throwable $e) {
            $logger->error(sprintf("[LastFM] error scrapping artist %s (%s): %s", $artist->name, $artist->mbId, $e->getMessage()));
        } finally {
            if ($success) {
                $dbh->commit();
            } else {
                $dbh->rollBack();
            }
        }
        if (!empty($artist->wikipediaURL)) {
            $success = false;
            // wikipedia block
            try {
                $wikipediaArtist = new \Spieldose\Scraper\Artist\Wikipedia($logger);
                if ($wikipediaArtist->scrapWikipedia($artist->wikipediaURL)) {
                    $dbh->beginTransaction();
                    $wikipediaArtist->saveCache($dbh);
                    $success = true;
                }
            } catch (\Throwable $e) {
                $logger->error(sprintf("[Wikipedia] error scrapping url %s: %s", $artist->wikipediaURL, $e->getMessage()));
            } finally {
                if ($success) {
                    $dbh->commit();
                } else {
                    die("ROLLBACXK1");
                    $dbh->rollBack();
                }
            }
        } else if (!empty($artist->wikidataURL)) {
            $success = false;
            // wikidata block (wikipedia failover)
            try {
                $wikipediaArtist = new \Spieldose\Scraper\Artist\Wikipedia($logger);
                if ($wikipediaArtist->scrapWikidata($artist->wikidataURL)) {
                    $dbh->beginTransaction();
                    $wikipediaArtist->saveCache($dbh);
                    $success = true;
                }
            } catch (\Throwable $e) {
                $logger->error(sprintf("[Wikidata] error scrapping url %s: %s", $artist->wikidataURL, $e->getMessage()));
            } finally {
                if ($success) {
                    $dbh->commit();
                } else {
                    $dbh->rollBack();
                }
            }
        }
    }

    private static function scrapWikipedia(\Psr\Log\LoggerInterface $logger, \aportela\DatabaseWrapper\DB $dbh, string $mbId): bool
    {

        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $mbId),
            new \aportela\DatabaseWrapper\Param\StringParam(":wikipedia_relation_type_id", \aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_WIKIPEDIA->value)
        );
        $query = "
            SELECT CAM.mbid, CAM.name, CAMUR.url
            FROM CACHE_ARTIST_MUSICBRAINZ CAM
            INNER JOIN CACHE_ARTIST_MUSICBRAINZ_URL_RELATIONSHIP CAMUR ON CAMUR.artist_mbid = CAM.mbid AND CAMUR.relation_type_id = :wikipedia_relation_type_id
            WHERE CAM.mbid = :mbid
            LIMIT 1
        ";
        $results = $dbh->query($query, $params);
        if (count($results) == 1) {
            try {
                $wikipediaArtist = new \Spieldose\Scraper\Artist\Wikipedia($logger);
                if ($wikipediaArtist->scrapWikipedia($results[0]->url)) {
                    $dbh->beginTransaction();
                    $wikipediaArtist->saveCache($dbh);
                    $success = true;
                }
            } catch (\Throwable $e) {
                $logger->error(sprintf("[Wikipedia] error scrapping artist %s (%s) url %s: %s", $results[0]->name, $results[0]->mbid, $results[0]->url, $e->getMessage()));
            } finally {
                if ($success) {
                    $dbh->commit();
                } else {
                    $dbh->rollBack();
                }
            }
        } else {
            return (false);
        }
    }

    private static function scrapWikidata(\Psr\Log\LoggerInterface $logger, \aportela\DatabaseWrapper\DB $dbh, string $mbId): bool
    {
        $success = false;
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $mbId),
            new \aportela\DatabaseWrapper\Param\StringParam(":wikidata_relation_type_id", \aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_WIKIDATA->value)
        );
        $query = "
            SELECT CAM.mbid, CAM.name, CAMUR.url
            FROM CACHE_ARTIST_MUSICBRAINZ CAM
            INNER JOIN CACHE_ARTIST_MUSICBRAINZ_URL_RELATIONSHIP CAMUR ON CAMUR.artist_mbid = CAM.mbid AND CAMUR.relation_type_id = :wikidata_relation_type_id
            WHERE CAM.mbid = :mbid
            LIMIT 1
        ";
        $results = $dbh->query($query, $params);
        if (count($results) == 1) {
            try {
                $wikipediaArtist = new \Spieldose\Scraper\Artist\Wikipedia($logger);
                if ($wikipediaArtist->scrapWikidata($results[0]->url)) {
                    $dbh->beginTransaction();
                    $wikipediaArtist->mbId = $results[0]->mbid;
                    $wikipediaArtist->saveCache($dbh);
                    $success = true;
                }
                return (true);
            } catch (\Throwable $e) {
                print_r($e);
                $logger->error(sprintf("[Wikidata] error scrapping artist %s (%s) url %s: %s", $results[0]->name, $results[0]->mbid, $results[0]->url, $e->getMessage()));
                return (false);
            } finally {
                if ($success) {
                    $dbh->commit();
                } else {
                    $dbh->rollBack();
                }
            }
        } else {
            return (false);
        }
    }

    public static function scrapWiki(\Psr\Log\LoggerInterface $logger, \aportela\DatabaseWrapper\DB $dbh, string $mbId): bool
    {
        return (self::scrapWikipedia($logger, $dbh, $mbId) || self::scrapWikidata($logger, $dbh, $mbId));
    }
}
