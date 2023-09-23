<?php

declare(strict_types=1);

namespace Spieldose\Scraper\Release;

class Scraper
{
    public static function getMusicBrainzReleaseIdsWithoutCache(\aportela\DatabaseWrapper\DB $dbh, bool $randomize = false)
    {
        $mbIds = array();
        $query = sprintf(
            "
                SELECT
                    DISTINCT FIT.mb_album_id AS mbid
                FROM FILE_ID3_TAG FIT
                WHERE FIT.mb_album_id IS NOT NULL
                AND NOT EXISTS
                    (SELECT CRM.mbid FROM CACHE_RELEASE_MUSICBRAINZ CRM WHERE CRM.mbid = FIT.mb_album_id)
                %s
            ",
            $randomize ? "ORDER BY RANDOM()" : ""
        );
        $results = $dbh->query($query);
        foreach ($results as $result) {
            $mbIds[] = $result->mbid;
        }
        return ($mbIds);
    }

    public static function scrap(\Psr\Log\LoggerInterface $logger, \aportela\DatabaseWrapper\DB $dbh, ?string $mbId): void
    {
        $success = false;
        $dbh->beginTransaction();
        try {
            // musicbrainz block
            $musicBrainzRelease = new \Spieldose\Scraper\Release\MusicBrainz(new \Psr\Log\NullLogger(), \aportela\MusicBrainzWrapper\APIFormat::JSON);
            $musicBrainzRelease->mbId = $mbId;
            if ($musicBrainzRelease->scrap()) {
                $logger->warning(sprintf("Saving cache of %s by %s (%s)", $musicBrainzRelease->title, $musicBrainzRelease->artist->name, $mbId));
                $musicBrainzRelease->saveCache($dbh);
            } else {
                $logger->warning(sprintf("Release %s not scraped", $mbId));
            }
            $success = true;
        } catch (\Throwable $e) {
            $logger->error(sprintf("Release scrap error: %s", $e->getMessage()));
        } finally {
            if ($success) {
                $dbh->commit();
            } else {
                $dbh->rollBack();
            }
        }
    }
}
