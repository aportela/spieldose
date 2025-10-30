<?php

declare(strict_types=1);

namespace Spieldose\Library\Scraper\MusicBrainz;

class ReleaseScraper
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;
    private \aportela\MusicBrainzWrapper\Release $musicBrainzReleaseAPI;
    private bool $refreshExistingCache = false;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger, ?string $mbCachePath = null, bool $refreshExistingCache = false)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->musicBrainzReleaseAPI = new \aportela\MusicBrainzWrapper\Release($logger, \aportela\MusicBrainzWrapper\APIFormat::JSON, \aportela\MusicBrainzWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, $mbCachePath, $refreshExistingCache);
        $this->refreshExistingCache = $refreshExistingCache;
    }

    public function __destruct() {}


    private function getReleaseMBIdsWithoutCache()
    {
        $mbIds = [];
        $results = $this->dbh->query(
            "
                SELECT DISTINCT
                    FILE_ID3_TAG.mb_release_id AS mbid
                FROM FILE_ID3_TAG
                LEFT JOIN CACHE_MUSICBRAINZ_RELEASE ON CACHE_MUSICBRAINZ_RELEASE.mbid = FILE_ID3_TAG.mb_release_id
                WHERE
                    FILE_ID3_TAG.mb_release_id IS NOT NULL
                AND
                    CACHE_MUSICBRAINZ_RELEASE.mbid IS NULL
            "
        );
        foreach ($results as $result) {
            $mbIds[] = $result->mbid;
        }
        return ($mbIds);
    }

    private function getAllReleaseMBIds()
    {
        $mbIds = [];
        $results = $this->dbh->query(
            "
                SELECT DISTINCT
                    FILE_ID3_TAG.mb_release_id AS mbid
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
    private function saveCache(\aportela\MusicBrainzWrapper\ParseHelpers\ReleaseHelper $release)
    {
        $this->dbh->execute(
            "
                INSERT INTO CACHE_MUSICBRAINZ_RELEASE
                    (mbid, title, year, ctime, mtime)
                VALUES
                    (:mbid, :title, :year, :current_timestamp, NULL)
                ON CONFLICT (mbid) DO
                    UPDATE SET
                        title = :title,
                        year = :year,
                        mtime = :current_timestamp
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $release->mbId),
                new \aportela\DatabaseWrapper\Param\StringParam(":title", $release->title),
                $release->year != null ?
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":year", $release->year)
                    :
                    new \aportela\DatabaseWrapper\Param\NullParam(":year"),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
            ]
        );
        $this->dbh->execute(
            "
                DELETE FROM CACHE_MUSICBRAINZ_RELEASE_ARTIST
                WHERE
                    release_mbid = :release_mbid
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":release_mbid", $release->mbId)
            ]
        );
        foreach ($release->artistCredit as $releaseArtist) {
            $this->dbh->execute(
                "
                    INSERT INTO CACHE_MUSICBRAINZ_RELEASE_ARTIST
                        (release_mbid, artist_mbid)
                    VALUES
                        (:release_mbid, :artist_mbid)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":release_mbid", $release->mbId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $releaseArtist->mbId)
                ]
            );
        }
    }

    public function scrapMissingCache(?callable $scrapItemCallback = null): float
    {
        $scanStartTime = microtime(true);
        $releaseMBIds = $this->refreshExistingCache ? $this->getAllReleaseMBIds() : $this->getReleaseMBIdsWithoutCache();
        $totalReleaseMbIds = count($releaseMBIds);
        for ($i = 0; $i < $totalReleaseMbIds; $i++) {
            if ($scrapItemCallback != null) {
                call_user_func($scrapItemCallback, $releaseMBIds, $totalReleaseMbIds, $i);
            }
            try {
                $release = $this->musicBrainzReleaseAPI->get($releaseMBIds[$i]);
                $this->saveCache($release);
            } catch (\aportela\MusicBrainzWrapper\Exception\NotFoundException $e) {
                $this->logger->warning("MusicBrainz release id get not found", [$releaseMBIds[$i], $e->getMessage()]);
            } catch (\aportela\MusicBrainzWrapper\Exception\RemoteAPIServerConnectionException $e) {
                $this->logger->warning("MusicBrainz API server (get release) not reachable", [$releaseMBIds[$i], $e->getMessage()]);
            } catch (\Throwable $e) {
                $this->logger->warning("MusicBrainz release id get error", [$releaseMBIds[$i], $e->getMessage(), $e->getPrevious()]);
            }
        }
        return (microtime(true) - $scanStartTime);
    }
}
