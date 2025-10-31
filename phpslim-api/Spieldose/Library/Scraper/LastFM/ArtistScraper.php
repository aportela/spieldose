<?php

declare(strict_types=1);

namespace Spieldose\Library\Scraper\LastFM;

class ArtistScraper
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;
    private \aportela\LastFMWrapper\Artist $lastFMArtistAPI;
    private bool $refreshExistingCache = false;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger, string $apiKey, ?string $mbCachePath = null, bool $refreshExistingCache = false)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->lastFMArtistAPI = new \aportela\LastFMWrapper\Artist($logger, \aportela\LastFMWrapper\APIFormat::JSON, $apiKey, \aportela\LastFMWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, $mbCachePath, $refreshExistingCache);
        $this->refreshExistingCache = $refreshExistingCache;
    }

    public function __destruct() {}

    private function getArtistNamesWithoutCache()
    {
        $names = [];
        $results = $this->dbh->query(
            "
                SELECT
                    DISTINCT FILE_ID3_TAG.artist AS name
                FROM FILE_ID3_TAG
                WHERE
                    FILE_ID3_TAG.artist IS NOT NULL
            "
        );
        foreach ($results as $result) {
            $names[] = $result->name;
        }
        return ($names);
    }

    private function getAllArtistNames()
    {
        $names = [];
        $results = $this->dbh->query(
            "
                SELECT
                    FILE_ID3_TAG.artist AS name
                FROM FILE_ID3_TAG
                WHERE
                    FILE_ID3_TAG.artist IS NOT NULL
                UNION
                SELECT
                    FILE_ID3_TAG.album_artist AS name
                FROM FILE_ID3_TAG
                WHERE
                    FILE_ID3_TAG.album_artist IS NOT NULL
                UNION
                SELECT
                    CACHE_MUSICBRAINZ_ARTIST.name
                FROM CACHE_MUSICBRAINZ_ARTIST
            "
        );
        foreach ($results as $result) {
            $names[] = $result->name;
        }
        return ($names);
    }

    /**
     * save LastFM artist cache (metadata/genres/relationships)
     */
    private function saveCache(\aportela\LastFMWrapper\ParseHelpers\ArtistHelper $artist)
    {
        $artistHash = md5($artist->name);
        $this->dbh->execute(
            "
                INSERT INTO CACHE_LASTFM_ARTIST
                    (md5_hash, mbid, name, url, image, bio_summary, bio_content, ctime, mtime)
                VALUES
                    (:md5_hash, :mbid, :name, :url, :image, :bio_summary, :bio_content, :current_timestamp, NULL)
                ON CONFLICT (md5_hash) DO
                    UPDATE SET
                        mbid = :mbid,
                        name = :name,
                        url = :url,
                        image = :image,
                        bio_summary = :bio_summary,
                        bio_content = :bio_content,
                        mtime = :current_timestamp
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":md5_hash", $artistHash),
                ! empty($artist->mbId) ?
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $artist->mbId)
                    :
                    new \aportela\DatabaseWrapper\Param\NullParam(":mbid"),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $artist->name),
                new \aportela\DatabaseWrapper\Param\StringParam(":url", $artist->url),
                ! empty($artist->image) ?
                    new \aportela\DatabaseWrapper\Param\StringParam(":image", $artist->image)
                    :
                    new \aportela\DatabaseWrapper\Param\NullParam(":image"),
                ! empty($artist->bio->summary) ?
                    new \aportela\DatabaseWrapper\Param\StringParam(":bio_summary", $artist->bio->summary)
                    :
                    new \aportela\DatabaseWrapper\Param\NullParam(":bio_summary"),
                ! empty($artist->bio->content) ?
                    new \aportela\DatabaseWrapper\Param\StringParam(":bio_content", $artist->bio->content)
                    :
                    new \aportela\DatabaseWrapper\Param\NullParam(":bio_content"),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
            ]
        );
        $this->dbh->execute(
            "
                DELETE FROM CACHE_LASTFM_ARTIST_TAG
                WHERE
                    artist_hash = :artist_hash
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":artist_hash", $artistHash),
            ]
        );
        foreach ($artist->tags as $tag) {
            $this->dbh->execute(
                "
                    INSERT INTO CACHE_LASTFM_ARTIST_TAG
                        (artist_hash, tag)
                    VALUES
                        (:artist_hash, :tag)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_hash", $artistHash),
                    new \aportela\DatabaseWrapper\Param\StringParam(":tag", $tag)
                ]
            );
        }
        $this->dbh->execute(
            "
                DELETE FROM CACHE_LASTFM_ARTIST_SIMILAR
                WHERE
                    artist_hash = :artist_hash
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":artist_hash", $artistHash),
            ]
        );
        foreach ($artist->similar as $similarArtist) {
            $this->dbh->execute(
                "
                    INSERT INTO CACHE_LASTFM_ARTIST_SIMILAR
                        (artist_hash, name)
                    VALUES
                        (:artist_hash, :name)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_hash", $artistHash),
                    new \aportela\DatabaseWrapper\Param\StringParam(":name", $similarArtist->name)
                ]
            );
        }
    }

    public function scrapMissingCache(?callable $scrapItemCallback = null): float
    {
        $scanStartTime = microtime(true);
        $artistLastFMNames = $this->refreshExistingCache ? $this->getAllArtistNames() : $this->getArtistNamesWithoutCache();
        $totalArtistLastFMNames = count($artistLastFMNames);
        for ($i = 0; $i < $totalArtistLastFMNames; $i++) {
            if ($scrapItemCallback != null) {
                call_user_func($scrapItemCallback, $artistLastFMNames, $totalArtistLastFMNames, $i);
            }
            try {
                $artist = $this->lastFMArtistAPI->get($artistLastFMNames[$i]);
                $this->saveCache($artist);
            } catch (\aportela\LastFMWrapper\Exception\NotFoundException $e) {
                $this->logger->warning("LastFM artist id get not found", [$artistLastFMNames[$i], $e->getMessage()]);
            } catch (\aportela\LastFMWrapper\Exception\RemoteAPIServerConnectionException $e) {
                $this->logger->warning("LastFM API server (get artist) not reachable", [$artistLastFMNames[$i], $e->getMessage()]);
            } catch (\Throwable $e) {
                $this->logger->warning("LastFM artist id get error", [$artistLastFMNames[$i], $e->getMessage(), $e->getPrevious()]);
            }
        }
        return (microtime(true) - $scanStartTime);
    }
}
