<?php

declare(strict_types=1);

namespace Spieldose\Library\Scraper\LastFM;

class AlbumScraper
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;
    private \aportela\LastFMWrapper\Album $lastFMAlbumAPI;
    private bool $refreshExistingCache = false;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger, string $apiKey, ?string $mbCachePath = null, bool $refreshExistingCache = false)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->lastFMAlbumAPI = new \aportela\LastFMWrapper\Album($logger, \aportela\LastFMWrapper\APIFormat::JSON, $apiKey, \aportela\LastFMWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, $mbCachePath, $refreshExistingCache);
        $this->refreshExistingCache = $refreshExistingCache;
    }

    public function __destruct() {}

    private function getMissingCacheAlbumsData()
    {
        return (
            $this->dbh->query(
                "
                    SELECT
                            DISTINCT COALESCE(FILE_ID3_TAG.album_artist, FILE_ID3_TAG.artist) AS artist, FILE_ID3_TAG.album
                    FROM FILE_ID3_TAG
                    WHERE
                        COALESCE(FILE_ID3_TAG.album_artist, FILE_ID3_TAG.artist) IS NOT NULL
                    AND
                        FILE_ID3_TAG.album IS NOT NULL
                    AND
                        NOT EXISTS (
                            SELECT
                                1
                            FROM CACHE_LASTFM_ARTIST
                            WHERE CACHE_LASTFM_ARTIST.name = COALESCE(FILE_ID3_TAG.album_artist, FILE_ID3_TAG.artist)
                        )
                "
            )
        );
    }

    private function getAllAlbumsData()
    {
        return (
            $this->dbh->query(
                // TODO: UNION MUSICBRAINZ EXISTING DATA
                "
                    SELECT
                        DISTINCT COALESCE(FILE_ID3_TAG.album_artist, FILE_ID3_TAG.artist) AS artist, FILE_ID3_TAG.album
                    FROM FILE_ID3_TAG
                    WHERE
                        COALESCE(FILE_ID3_TAG.album_artist, FILE_ID3_TAG.artist) IS NOT NULL
                    AND
                        FILE_ID3_TAG.album IS NOT NULL
                "
            )
        );
    }

    /**
     * save LastFM album cache (metadata/tags/tracks)
     */
    private function saveCache(\aportela\LastFMWrapper\ParseHelpers\AlbumHelper $album)
    {
        $albumHash = md5($album->artist->name . $album->name);
        $this->dbh->execute(
            "
                INSERT INTO CACHE_LASTFM_ALBUM
                    (md5_hash, mbid, name, artist_name, url, ctime, mtime)
                VALUES
                    (:md5_hash, :mbid, :name, :artist_name, :url, :current_timestamp, NULL)
                ON CONFLICT (md5_hash) DO
                    UPDATE SET
                        name = :name,
                        mbid = :mbid,
                        artist_name = :artist_name,
                        url = :url,
                        mtime = :current_timestamp
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":md5_hash", md5($album->artist->name . $album->name)),
                ! empty($album->mbId) ?
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $album->mbId)
                    :
                    new \aportela\DatabaseWrapper\Param\NullParam("mbid"),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $album->name),
                new \aportela\DatabaseWrapper\Param\StringParam(":artist_name", $album->artist->name),
                new \aportela\DatabaseWrapper\Param\StringParam(":url", $album->url),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
            ]
        );
        $this->dbh->execute(
            "
                DELETE FROM CACHE_LASTFM_ALBUM_TAG
                WHERE
                    album_hash = :album_hash
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":album_hash", $albumHash),
            ]
        );
        foreach ($album->tags as $tag) {
            $this->dbh->execute(
                "
                    INSERT INTO CACHE_LASTFM_ALBUM_TAG
                        (album_hash, tag)
                    VALUES
                        (:album_hash, :tag)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":album_hash", $albumHash),
                    new \aportela\DatabaseWrapper\Param\StringParam(":tag", $tag)
                ]
            );
        }
    }

    public function scrapMissingCache(?callable $scrapItemCallback = null): float
    {
        $scanStartTime = microtime(true);
        $albumsData = $this->refreshExistingCache ? $this->getAllAlbumsData() : $this->getMissingCacheAlbumsData();
        $totalAlbumsData = count($albumsData);
        for ($i = 0; $i < $totalAlbumsData; $i++) {
            if ($scrapItemCallback != null) {
                call_user_func($scrapItemCallback, $albumsData, $totalAlbumsData, $i);
            }
            try {
                $album = $this->lastFMAlbumAPI->get($albumsData[$i]->artist, $albumsData[$i]->album);
                $this->saveCache($album);
            } catch (\aportela\LastFMWrapper\Exception\NotFoundException $e) {
                $this->logger->warning("LastFM album id get not found", [$albumsData[$i], $e->getMessage()]);
            } catch (\aportela\LastFMWrapper\Exception\RemoteAPIServerConnectionException $e) {
                $this->logger->warning("LastFM API server (get album) not reachable", [$albumsData[$i], $e->getMessage()]);
            } catch (\Throwable $e) {
                $this->logger->warning("LastFM album id get error", [$albumsData[$i], $e->getMessage(), $e->getPrevious()]);
            }
        }
        return (microtime(true) - $scanStartTime);
    }
}
