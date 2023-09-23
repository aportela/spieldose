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

    public static function getMusicBrainzArtistIdsWithoutCache(\aportela\DatabaseWrapper\DB $dbh, bool $randomize = false)
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

    public static function scrap(\Psr\Log\LoggerInterface $logger, \aportela\DatabaseWrapper\DB $dbh, string $lastFMAPIKey, ?string $mbId, ?string $name): void
    {
        $success = false;
        $dbh->beginTransaction();
        try {
            // musicbrainz block
            $musicBrainzArtist = new \Spieldose\Scraper\Artist\MusicBrainz(new \Psr\Log\NullLogger(), \aportela\MusicBrainzWrapper\APIFormat::JSON);
            $musicBrainzArtist->mbId = $mbId;
            $musicBrainzArtist->name = $name;
            if ($musicBrainzArtist->scrap()) {
                $musicBrainzArtist->fixTags($dbh);
                $logger->warning(sprintf("Saving Musicbrainz cache of %s (%s)", $musicBrainzArtist->name, $mbId));
                $musicBrainzArtist->saveCache($dbh);
            } else {
                $logger->warning(sprintf("Musicbrainz artist (%s) not scraped", $mbId));
            }
            // last.fm block
            $lastFMArtist = new \Spieldose\Scraper\Artist\LastFM(new \Psr\Log\NullLogger(), \aportela\LastFMWrapper\APIFormat::JSON, $lastFMAPIKey);
            $lastFMArtist->name = $musicBrainzArtist->name ?? $name;
            if ($lastFMArtist->scrap()) {
                $logger->warning(sprintf("Saving LastFM cache of %s (%s)", $musicBrainzArtist->name, $mbId));
                $lastFMArtist->saveCache($dbh);
            } else {
                $logger->warning(sprintf("LastFM artist (%s) not scraped", $mbId));
            }
            // TODO wikipedia block
            $success = true;
        } catch (\Throwable $e) {
            $logger->error(sprintf("Artist scrap error: %s", $e->getMessage()));
        } finally {
            if ($success) {
                $dbh->commit();
            } else {
                $dbh->rollBack();
            }
        }
    }
}
