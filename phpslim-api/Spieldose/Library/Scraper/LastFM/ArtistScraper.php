<?php

declare(strict_types=1);

namespace Spieldose\Library\Scraper\LastFM;

class ArtistScraper
{
    private readonly \aportela\LastFMWrapper\Artist $artist;

    public function __construct(private readonly \aportela\DatabaseWrapper\DB $db, private readonly \Psr\Log\LoggerInterface $logger, string $apiKey, \aportela\SimpleFSCache\Cache $cache)
    {
        $this->artist = new \aportela\LastFMWrapper\Artist($this->logger, \aportela\LastFMWrapper\APIFormat::JSON, $apiKey, \aportela\LastFMWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, $cache);
    }

    /**
     * @return mixed[]
     */
    private function getArtistNamesWithoutCache(): array
    {
        $names = [];
        $results = $this->db->query(
            "
                SELECT
                    DISTINCT FILE_ID3_TAG.artist AS name
                FROM FILE_ID3_TAG
                LEFT JOIN CACHE_LASTFM_ARTIST ON CACHE_LASTFM_ARTIST.name = FILE_ID3_TAG.artist
                WHERE
                    FILE_ID3_TAG.artist IS NOT NULL
                AND
                    CACHE_LASTFM_ARTIST.name IS NULL
                UNION
                SELECT
                    DISTINCT FILE_ID3_TAG.album_artist AS name
                FROM FILE_ID3_TAG
                LEFT JOIN CACHE_LASTFM_ARTIST ON CACHE_LASTFM_ARTIST.name = FILE_ID3_TAG.album_artist
                WHERE
                    FILE_ID3_TAG.album_artist IS NOT NULL
                AND
                    CACHE_LASTFM_ARTIST.name IS NULL
                UNION
                SELECT
                    CACHE_MUSICBRAINZ_ARTIST.name
                FROM CACHE_MUSICBRAINZ_ARTIST
                LEFT JOIN CACHE_LASTFM_ARTIST ON CACHE_LASTFM_ARTIST.name = CACHE_MUSICBRAINZ_ARTIST.name
                WHERE
                    CACHE_LASTFM_ARTIST.name IS NULL
            "
        );
        foreach ($results as $result) {
            $names[] = $result->name;
        }

        return ($names);
    }

    /**
     * @return mixed[]
     */
    private function getAllArtistNames(): array
    {
        $names = [];
        $results = $this->db->query(
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

    public function hasCache(string $name): bool
    {
        $results = $this->db->query(
            "
                SELECT
                    COUNT(md5_hash) AS total
                FROM CACHE_LASTFM_ARTIST
                WHERE md5_hash = :md5_hash
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":md5_hash", $this->artist->getHash($name)),
            ]
        );
        return (intval($results[0]->total) === 1);
    }

    /**
     * save LastFM artist cache (metadata/genres/relationships)
     */
    private function saveCache(\aportela\LastFMWrapper\ParseHelpers\ArtistHelper $artistHelper): void
    {
        $artistHash = md5((string) $artistHelper->name);
        $this->db->execute(
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
                in_array($artistHelper->mbId, [null, '', '0'], true)
                    ? new \aportela\DatabaseWrapper\Param\NullParam(":mbid")
                    : new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $artistHelper->mbId),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $artistHelper->name),
                new \aportela\DatabaseWrapper\Param\StringParam(":url", $artistHelper->url),
                in_array($artistHelper->image, [null, '', '0'], true)
                    ? new \aportela\DatabaseWrapper\Param\NullParam(":image")
                    : new \aportela\DatabaseWrapper\Param\StringParam(":image", $artistHelper->image),
                empty($artistHelper->bio->summary)
                    ? new \aportela\DatabaseWrapper\Param\NullParam(":bio_summary")
                    : new \aportela\DatabaseWrapper\Param\StringParam(":bio_summary", $artistHelper->bio->summary),
                empty($artistHelper->bio->content)
                    ? new \aportela\DatabaseWrapper\Param\NullParam(":bio_content")
                    : new \aportela\DatabaseWrapper\Param\StringParam(":bio_content", $artistHelper->bio->content),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
            ]
        );
        $this->db->execute(
            "
                DELETE FROM CACHE_LASTFM_ARTIST_TAG
                WHERE
                    artist_hash = :artist_hash
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":artist_hash", $artistHash),
            ]
        );
        foreach ($artistHelper->tags as $tag) {
            $this->db->execute(
                "
                    INSERT INTO CACHE_LASTFM_ARTIST_TAG
                        (artist_hash, tag)
                    VALUES
                        (:artist_hash, :tag)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_hash", $artistHash),
                    new \aportela\DatabaseWrapper\Param\StringParam(":tag", $tag),
                ]
            );
        }

        $this->db->execute(
            "
                DELETE FROM CACHE_LASTFM_ARTIST_SIMILAR
                WHERE
                    artist_hash = :artist_hash
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":artist_hash", $artistHash),
            ]
        );
        foreach ($artistHelper->similar as $similarArtist) {
            $this->db->execute(
                "
                    INSERT INTO CACHE_LASTFM_ARTIST_SIMILAR
                        (artist_hash, name)
                    VALUES
                        (:artist_hash, :name)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_hash", $artistHash),
                    new \aportela\DatabaseWrapper\Param\StringParam(":name", $similarArtist->name),
                ]
            );
        }
    }

    public function scrapMissingCache(?callable $scrapItemCallback = null, bool $force = false): float
    {
        $scanStartTime = microtime(true);
        $artistLastFMNames = $force ? $this->getAllArtistNames() : $this->getArtistNamesWithoutCache();
        $totalArtistLastFMNames = count($artistLastFMNames);
        for ($i = 0; $i < $totalArtistLastFMNames; ++$i) {
            if ($scrapItemCallback != null) {
                call_user_func($scrapItemCallback, $artistLastFMNames, $totalArtistLastFMNames, $i);
            }

            try {
                $artist = $this->artist->get($artistLastFMNames[$i]);
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
