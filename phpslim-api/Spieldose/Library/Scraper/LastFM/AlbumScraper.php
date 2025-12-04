<?php

declare(strict_types=1);

namespace Spieldose\Library\Scraper\LastFM;

class AlbumScraper
{
    private readonly \aportela\LastFMWrapper\Album $album;

    public function __construct(private readonly \aportela\DatabaseWrapper\DB $db, private readonly \Psr\Log\LoggerInterface $logger, string $apiKey, \aportela\SimpleFSCache\Cache $cache)
    {
        $this->album = new \aportela\LastFMWrapper\Album($this->logger, \aportela\LastFMWrapper\APIFormat::JSON, $apiKey, \aportela\LastFMWrapper\Entity::DEFAULT_THROTTLE_DELAY_MS, $cache);
    }

    private function getMissingCacheAlbumsData()
    {
        return (
            $this->db->query(
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
                            FROM CACHE_LASTFM_ALBUM
                            WHERE lower(trim(CACHE_LASTFM_ALBUM.name)) = lower(trim(FILE_ID3_TAG.album))
                            AND lower(trim(CACHE_LASTFM_ALBUM.artist_name)) = lower(trim(COALESCE(FILE_ID3_TAG.album_artist, FILE_ID3_TAG.artist)))
                        )
                "
            )
        );
    }

    private function getAllAlbumsData()
    {
        return (
            $this->db->query(
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
    private function saveCache(\aportela\LastFMWrapper\ParseHelpers\AlbumHelper $albumHelper): void
    {
        $albumHash = md5(mb_strtolower(mb_trim($albumHelper->artist->name)) . mb_strtolower(mb_trim($albumHelper->name)));
        $this->db->execute(
            "
                INSERT INTO CACHE_LASTFM_ALBUM
                    (md5_hash, mbid, name, artist_name, url, wiki_summary, wiki_content, ctime, mtime)
                VALUES
                    (:md5_hash, :mbid, :name, :artist_name, :url, :wiki_summary, :wiki_content, :current_timestamp, NULL)
                ON CONFLICT (md5_hash) DO
                    UPDATE SET
                        name = :name,
                        mbid = :mbid,
                        artist_name = :artist_name,
                        url = :url,
                        wiki_summary = :wiki_summary,
                        wiki_content = :wiki_content,
                        mtime = :current_timestamp
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":md5_hash", $albumHash),
                in_array($albumHelper->mbId, [null, '', '0'], true)
                    ? new \aportela\DatabaseWrapper\Param\NullParam("mbid")
                    : new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $albumHelper->mbId),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $albumHelper->name),
                new \aportela\DatabaseWrapper\Param\StringParam(":artist_name", $albumHelper->artist->name),
                new \aportela\DatabaseWrapper\Param\StringParam(":url", $albumHelper->url),
                empty($albumHelper->wiki->summary)
                    ? new \aportela\DatabaseWrapper\Param\NullParam("wiki_summary")
                    : new \aportela\DatabaseWrapper\Param\StringParam(":wiki_summary", $albumHelper->wiki->summary),
                empty($albumHelper->wiki->content)
                    ? new \aportela\DatabaseWrapper\Param\NullParam("wiki_content")
                    : new \aportela\DatabaseWrapper\Param\StringParam(":wiki_content", $albumHelper->wiki->content),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
            ]
        );
        $this->db->execute(
            "
                DELETE FROM CACHE_LASTFM_ALBUM_TAG
                WHERE
                    album_hash = :album_hash
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":album_hash", $albumHash),
            ]
        );
        foreach ($albumHelper->tags as $tag) {
            $this->db->execute(
                "
                    INSERT INTO CACHE_LASTFM_ALBUM_TAG
                        (album_hash, tag)
                    VALUES
                        (:album_hash, :tag)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":album_hash", $albumHash),
                    new \aportela\DatabaseWrapper\Param\StringParam(":tag", $tag),
                ]
            );
        }

        $this->db->execute(
            "
                DELETE FROM CACHE_LASTFM_ALBUM_TRACK
                WHERE
                    album_hash = :album_hash
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":album_hash", $albumHash),
            ]
        );
        foreach ($albumHelper->tracks as $track) {
            $trackHash = md5($albumHash . mb_strtolower(mb_trim($track->name)) . mb_strtolower(mb_trim($track->artist->name)));
            $this->db->execute(
                "
                    INSERT INTO CACHE_LASTFM_ALBUM_TRACK
                        (md5_hash, album_hash, name, artist_name, rank)
                    VALUES
                        (:md5_hash, :album_hash, :name, :artist_name, :rank)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":md5_hash", $trackHash),
                    new \aportela\DatabaseWrapper\Param\StringParam(":album_hash", $albumHash),
                    new \aportela\DatabaseWrapper\Param\StringParam(":name", $track->name),
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_name", $track->artist->name),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":rank", $track->rank),
                ]
            );
        }
    }

    public function scrapMissingCache(?callable $scrapItemCallback = null, bool $force = false): float
    {
        $scanStartTime = microtime(true);
        $albumsData = $force ? $this->getAllAlbumsData() : $this->getMissingCacheAlbumsData();
        $totalAlbumsData = count($albumsData);
        for ($i = 0; $i < $totalAlbumsData; ++$i) {
            if ($scrapItemCallback != null) {
                call_user_func($scrapItemCallback, $albumsData, $totalAlbumsData, $i);
            }

            try {
                $album = $this->album->get($albumsData[$i]->artist, $albumsData[$i]->album);
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
