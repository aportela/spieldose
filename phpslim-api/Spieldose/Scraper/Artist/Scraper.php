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
                    (SELECT CAL.name FROM CACHE_ARTIST_LASTFM CAL WHERE CAL.name = TMP_ARTISTS.name AND CAL.mbid = TMP_ARTISTS.mbid)
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
            "name" => $name ?? null,
            "wikipediaURL" => null,
            "wikidataURL" => null
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
}
