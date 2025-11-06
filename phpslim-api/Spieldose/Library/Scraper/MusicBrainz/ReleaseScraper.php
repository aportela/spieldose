<?php

declare(strict_types=1);

namespace Spieldose\Library\Scraper\MusicBrainz;

class ReleaseScraper
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;
    private \aportela\MusicBrainzWrapper\Release $musicBrainzReleaseAPI;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger, \aportela\SimpleFSCache\Cache $cache)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->musicBrainzReleaseAPI = new \aportela\MusicBrainzWrapper\Release($logger, \aportela\MusicBrainzWrapper\APIFormat::JSON, \aportela\MusicBrainzWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, $cache);
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
        $this->dbh->execute(
            "
                DELETE FROM CACHE_MUSICBRAINZ_MEDIA
                WHERE
                    release_mbid = :release_mbid
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":release_mbid", $release->mbId)
            ]
        );
        foreach ($release->media as $media) {
            $this->dbh->execute(
                "
                    INSERT INTO CACHE_MUSICBRAINZ_MEDIA
                        (mbid, release_mbid, position, ctime, mtime)
                    VALUES
                        (:mbid, :release_mbid, :position, :current_timestamp, NULL)
                    ON CONFLICT (mbid) DO
                    UPDATE SET
                        release_mbid = :release_mbid,
                        position = :position,
                        mtime = :current_timestamp
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $media->mbId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":release_mbid", $release->mbId),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":position", $media->position),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
                ]
            );
            foreach ($media->trackList as $track) {
                $this->dbh->execute(
                    "
                        INSERT INTO CACHE_MUSICBRAINZ_RECORDING
                            (mbid, title, ctime, mtime)
                        VALUES
                            (:mbid, :title, :current_timestamp, NULL)
                        ON CONFLICT (mbid) DO
                        UPDATE SET
                            title = :title,
                            mtime = :current_timestamp
                    ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $track->recording->mbId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":title", $track->recording->title),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
                    ]
                );
                $this->dbh->execute(
                    "
                        DELETE FROM CACHE_MUSICBRAINZ_RECORDING_ARTIST
                        WHERE
                            recording_mbid = :recording_mbid
                    ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":recording_mbid", $track->recording->mbId)
                    ]
                );
                foreach ($track->recording->artistCredit as $recordingArtist) {
                    $this->dbh->execute(
                        "
                            INSERT INTO CACHE_MUSICBRAINZ_RECORDING_ARTIST
                                (recording_mbid, artist_mbid)
                            VALUES
                                (:recording_mbid, :artist_mbid)
                        ",
                        [
                            new \aportela\DatabaseWrapper\Param\StringParam(":recording_mbid", $track->recording->mbId),
                            new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $recordingArtist->mbId)
                        ]
                    );
                }
                $this->dbh->execute(
                    "
                        DELETE FROM CACHE_MUSICBRAINZ_TRACK
                        WHERE
                            media_mbid = :media_mbid
                    ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":media_mbid", $media->mbId)
                    ]
                );
                $this->dbh->execute(
                    "
                        INSERT INTO CACHE_MUSICBRAINZ_TRACK
                            (mbid, media_mbid, recording_mbid, position, ctime, mtime)
                        VALUES
                            (:mbid, :media_mbid, :recording_mbid, :position, :current_timestamp, NULL)
                        ON CONFLICT (mbid) DO
                            UPDATE SET
                                media_mbid = :media_mbid,
                                recording_mbid = :recording_mbid,
                                position = :position,
                                mtime = :current_timestamp
                    ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $track->mbId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":media_mbid", $media->mbId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":recording_mbid", $track->recording->mbId),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":position", $track->position),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
                    ]
                );
            }
        }
    }

    public function scrapMissingCache(?callable $scrapItemCallback = null, bool $force = false): float
    {
        $scanStartTime = microtime(true);
        $releaseMBIds = $force ? $this->getAllReleaseMBIds() : $this->getReleaseMBIdsWithoutCache();
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
