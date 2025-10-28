<?php

declare(strict_types=1);

namespace Spieldose\Library\Scraper;

class MusicBrainzReleaseScraper
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;
    private \aportela\MusicBrainzWrapper\Release $mbRelease;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger, ?string $mbCachePath = null)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->mbRelease = new \aportela\MusicBrainzWrapper\Release($logger, \aportela\MusicBrainzWrapper\APIFormat::JSON, 1000, $mbCachePath);
    }

    public function __destruct() {}


    private function getMissingCacheReleaseMBIds()
    {
        $mbIds = [];
        $results = $this->dbh->query(
            "
                SELECT
                    FILE_ID3_TAG.mb_release_id AS mbid
                FROM FILE_ID3_TAG
                LEFT JOIN CACHE_RELEASE_MUSICBRAINZ ON CACHE_RELEASE_MUSICBRAINZ.mbid = FILE_ID3_TAG.mb_release_id
                WHERE
                    FILE_ID3_TAG.mb_release_id IS NOT NULL
                AND
                    CACHE_RELEASE_MUSICBRAINZ.mbid IS NULL
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
    private function saveMBCacheRelease()
    {
        $this->dbh->execute(
            "
                INSERT INTO CACHE_RELEASE_MUSICBRAINZ
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
                new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbRelease->mbId),
                new \aportela\DatabaseWrapper\Param\StringParam(":title", $this->mbRelease->title),
                $this->mbRelease->year != null ?
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":year", $this->mbRelease->year)
                    :
                    new \aportela\DatabaseWrapper\Param\NullParam(":year"),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
            ]
        );
        $this->dbh->execute(
            "
                DELETE FROM CACHE_RELEASE_ARTIST_MUSICBRAINZ
                WHERE
                    release_mbid = :release_mbid
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":release_mbid", $this->mbRelease->mbId)
            ]
        );
        foreach ($this->mbRelease->artistCredit as $releaseArtist) {
            $this->dbh->execute(
                "
                    INSERT INTO CACHE_RELEASE_ARTIST_MUSICBRAINZ
                        (release_mbid, artist_mbid)
                    VALUES
                        (:release_mbid, :artist_mbid)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":release_mbid", $this->mbRelease->mbId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $releaseArtist->mbId)
                ]
            );
        }
    }

    public function scrapMissingCache(?callable $scrapItemCallback = null): float
    {
        $scanStartTime = microtime(true);
        $releaseMBIds = $this->getMissingCacheReleaseMBIds();
        $totalReleaseMbIds = count($releaseMBIds);
        for ($i = 0; $i < $totalReleaseMbIds; $i++) {
            if ($scrapItemCallback != null) {
                call_user_func($scrapItemCallback, $releaseMBIds, $totalReleaseMbIds, $i);
            }
            try {
                $this->mbRelease->get($releaseMBIds[$i]);
                $this->saveMBCacheRelease();
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
