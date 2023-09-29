<?php

declare(strict_types=1);

namespace Spieldose;

class Lyrics
{
    public string $hash;
    public string $title;
    public string $artist;
    public ?string $data;
    public string $source;

    public function __construct(string $title, string $artist)
    {
        $this->title = trim($title);
        // ugly hack to scrap "live versions"
        $this->title = preg_replace("/ \(live\)$/i", "", $this->title);
        $this->artist = trim($artist);
        $this->hash = md5($this->title . $this->artist);
        $this->data = null;
    }

    public function __destruct()
    {
    }

    private function save(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $query = " INSERT INTO LYRICS (md5_hash, title, artist, data, source, ctime, mtime) VALUES (:md5_hash, :title, :artist, :data, :source, strftime('%s', 'now'), strftime('%s', 'now')) ON CONFLICT (md5_hash) DO UPDATE SET data = :data, source = :source, mtime = strftime('%s', 'now') ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":md5_hash", $this->hash),
            new \aportela\DatabaseWrapper\Param\StringParam(":title", $this->title),
            new \aportela\DatabaseWrapper\Param\StringParam(":artist", $this->artist),
            new \aportela\DatabaseWrapper\Param\StringParam(":data", $this->data),
            new \aportela\DatabaseWrapper\Param\StringParam(":source", $this->source)
        );
        $dbh->exec($query, $params);
    }

    private function scrap(\Psr\Log\LoggerInterface $logger)
    {
        $lyrics = new \aportela\ScraperLyrics\Lyrics($logger);
        if ($lyrics->scrap(
            $this->title,
            $this->artist,
            [
                \aportela\ScraperLyrics\SourceProvider::SEARCH_ENGINE_DUCKDUCKGO,
                \aportela\ScraperLyrics\SourceProvider::SEARCH_ENGINE_GOOGLE,
                \aportela\ScraperLyrics\SourceProvider::SEARCH_ENGINE_BING
            ]
        )) {
            $this->title = $lyrics->title;
            $this->artist = $lyrics->artist;
            $this->hash = md5($this->title . $this->artist);
            $this->data = $lyrics->lyrics;
            $this->source = $lyrics->source;
            return (true);
        } else {
            return (false);
        }
    }

    public function get(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger): bool
    {
        if (!empty($this->title)) {
            if (!empty($this->artist)) {
                $query = " SELECT data, source FROM LYRICS WHERE md5_hash = :md5_hash ";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":md5_hash", $this->hash)
                );
                $results = $dbh->query($query, $params);
                if (count($results) == 1) {
                    $this->data = $results[0]->data;
                    $this->source = $results[0]->source;
                    return (true);
                } else {
                    if ($this->scrap($logger)) {
                        $this->save($dbh);
                        return (true);
                    } else {
                        return (false);
                    }
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("artist");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("title");
        }
    }
}
