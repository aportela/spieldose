<?php

declare(strict_types=1);

namespace Spieldose\Scraper\Artist;

class LastFM
{
    private \Psr\Log\LoggerInterface $logger;
    private \aportela\LastFMWrapper\APIFormat $apiFormat;
    private string $apiKey;
    private bool $scraped;

    public ?string $mbId;
    public string $name;
    public string $url;
    public ?string $image;
    public ?string $bioSummary;
    public ?string $bioContent;

    public array $similar;
    public array $tags;


    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\LastFMWrapper\APIFormat $apiFormat, string $apiKey)
    {
        $this->logger = $logger;
        $this->apiFormat = $apiFormat;
        $this->apiKey = $apiKey;
        $this->scraped = false;
        $this->mbId = null;
        $this->bioSummary = null;
        $this->bioContent = null;
    }

    public function isScraped(): bool
    {
        return ($this->scraped);
    }

    public function scrap(): bool
    {
        $this->scraped = false;
        if (!empty($this->name)) {
            $lastFMArtist = new \aportela\LastFMWrapper\Artist($this->logger, $this->apiFormat, $this->apiKey);
            try {
                $lastFMArtist->get($this->name);
                $this->name = $lastFMArtist->name;
                $this->url = $lastFMArtist->url;
                $this->image = $lastFMArtist->getImageFromArtistPageURL($lastFMArtist->url);
                $this->tags = $lastFMArtist->tags ?? [];
                $this->similar = $lastFMArtist->similar ?? [];
                $this->bioSummary = $lastFMArtist->bio->summary;
                if (!empty($this->bioSummary)) {
                    // trim "read more on last fm"
                    $pattern = '/<a href="https:\/\/www\.last\.fm\/.*">Read more on Last.fm<\/a>$/i';
                    $this->bioSummary = preg_replace($pattern, "", $this->bioSummary);
                }
                $this->bioContent = $lastFMArtist->bio->content;
                if (!empty($this->bioContent)) {
                    // trim "read more on last fm"
                    $pattern = '/<a href="https:\/\/www\.last\.fm\/.*">Read more on Last.fm<\/a>. /i';
                    $this->bioContent = preg_replace($pattern, PHP_EOL . PHP_EOL, $this->bioContent);
                }
                $this->scraped = true;
            } catch (\Throwable $e) {
                $this->logger->warning(sprintf("[LastFM] error scraping mbid %s: %s", $this->mbId, $e->getMessage()));
            }
        }
        return ($this->scraped);
    }

    public function saveCache(\aportela\DatabaseWrapper\DB $dbh)
    {
        $query = "
            INSERT INTO CACHE_ARTIST_LASTFM (sha256_hash, mbid, name, url, image, bio_summary, bio_content, ctime, mtime) VALUES (:sha256_hash, :mbid, :name, :url, :image, :bio_summary, :bio_content, strftime('%s', 'now'), strftime('%s', 'now'))
                ON CONFLICT(sha256_hash) DO
            UPDATE SET name = :name, url =:url, image = :image, bio_summary = :bio_summary, bio_content = :bio_content, mtime = strftime('%s', 'now')
        ";
        $artistHash = hash("sha256", $this->mbId . $this->name);
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":sha256_hash", $artistHash),
            new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name),
            new \aportela\DatabaseWrapper\Param\StringParam(":url", $this->url),
        );
        if (!empty($this->mbId)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mbid");
        }
        if (!empty($this->image)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":image", $this->image);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":image");
        }
        if (!empty($this->bioSummary)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":bio_summary", trim($this->bioSummary));
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":bio_summary");
        }
        if (!empty($this->bioContent)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":bio_content", trim($this->bioContent));
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":bio_content");
        }
        $dbh->exec($query, $params);
        $query = "
            DELETE FROM CACHE_ARTIST_LASTFM_TAG WHERE artist_hash = :sha256_hash
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":sha256_hash", $artistHash)
        );
        $dbh->exec($query, $params);
        if (is_array($this->tags) && count($this->tags) > 0) {
            foreach ($this->tags as $tag) {
                $query = "
                    INSERT INTO CACHE_ARTIST_LASTFM_TAG (artist_hash, tag) VALUES (:sha256_hash, :tag)
                ";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":sha256_hash", $artistHash),
                    new \aportela\DatabaseWrapper\Param\StringParam(":tag", mb_strtolower($tag))
                );
                $dbh->exec($query, $params);
            }
        }
        $query = "
            DELETE FROM CACHE_ARTIST_LASTFM_SIMILAR WHERE artist_hash = :sha256_hash
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":sha256_hash", $artistHash)
        );
        $dbh->exec($query, $params);
        if (is_array($this->similar) && count($this->similar) > 0) {
            foreach ($this->similar as $artist) {
                $query = "
                    INSERT INTO CACHE_ARTIST_LASTFM_SIMILAR (artist_hash, name) VALUES (:sha256_hash, :name)
                ";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":sha256_hash", $artistHash),
                    new \aportela\DatabaseWrapper\Param\StringParam(":name", $artist->name)
                );
                $dbh->exec($query, $params);
            }
        }
    }
}
