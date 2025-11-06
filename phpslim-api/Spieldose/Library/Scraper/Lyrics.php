<?php

declare(strict_types=1);

namespace Spieldose\Library\Scraper;

class Lyrics
{

    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;
    private \aportela\ScraperLyrics\Lyrics $lyrics;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger, \aportela\SimpleFSCache\Cache $cache)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->lyrics = new \aportela\ScraperLyrics\Lyrics($logger, $cache);
    }

    public function __destruct() {}

    private function getAllTracks(): array
    {
        return (
            $this->dbh->query(
                // TODO: UNION MUSICBRAINZ EXISTING DATA
                "
                    SELECT
                        DISTINCT FILE_ID3_TAG.artist AS artist, FILE_ID3_TAG.title
                    FROM FILE_ID3_TAG
                    WHERE
                        FILE_ID3_TAG.artist IS NOT NULL
                    AND
                        FILE_ID3_TAG.title IS NOT NULL
                "
            )
        );
    }

    private function getTracksWithoutCache(): array
    {
        return (
            $this->dbh->query(
                // TODO: UNION MUSICBRAINZ EXISTING DATA
                "
                    SELECT
                        DISTINCT FILE_ID3_TAG.artist AS artist, FILE_ID3_TAG.title
                    FROM FILE_ID3_TAG
                    WHERE
                        FILE_ID3_TAG.artist IS NOT NULL
                    AND
                        FILE_ID3_TAG.title IS NOT NULL
                "
            )
        );
    }

    private function saveCache(string $title, string $artist, string $lyrics, string $source)
    {
        $this->dbh->execute(
            "
                INSERT INTO CACHE_LYRICS
                    (title, artist, lyrics, source, ctime, mtime)
                VALUES
                    (:title, :artist, :lyrics, :source, :current_timestamp, NULL)
                ON CONFLICT (title, artist) DO
                    UPDATE SET
                        lyrics = :lyrics,
                        source = :source,
                        mtime = :current_timestamp
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":title", $title),
                new \aportela\DatabaseWrapper\Param\StringParam(":artist", $artist),
                new \aportela\DatabaseWrapper\Param\StringParam(":lyrics", $lyrics),
                new \aportela\DatabaseWrapper\Param\StringParam(":source", $source),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
            ]
        );
    }

    public function scrapMissingCache(?callable $scrapItemCallback = null, bool $force = false): float
    {
        $scanStartTime = microtime(true);
        $tracks = $force ? $this->getAllTracks() : $this->getTracksWithoutCache();
        $totalTracks = count($tracks);
        for ($i = 0; $i < $totalTracks; $i++) {
            if ($scrapItemCallback != null) {
                call_user_func($scrapItemCallback, $tracks, $totalTracks, $i);
            }
            try {
                if ($this->lyrics->scrap($tracks[$i]->title, $tracks[$i]->artist)) {
                    $this->saveCache($this->lyrics->getTitle(), $this->lyrics->getArtist(), $this->lyrics->getLyrics(), $this->lyrics->getSource());
                } else {
                    $this->logger->warning("Error getting lyrics", [$tracks[$i]->title, $tracks[$i]->artist]);
                }
            } catch (\Throwable $e) {
                $this->logger->error("Lyrics get unhandled exception", [$tracks[$i]->title, $tracks[$i]->artist, $e->getMessage(), $e->getPrevious()]);
            }
        }
        return (microtime(true) - $scanStartTime);
    }
}
