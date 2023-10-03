<?php

declare(strict_types=1);

namespace Spieldose;

class Lyrics extends \aportela\ScraperLyrics\Lyrics
{

    private function save(\aportela\DatabaseWrapper\DB $dbh): void
    {
        $query = " INSERT INTO LYRICS (title, artist, data, source, ctime, mtime) VALUES (:title, :artist, :data, :source, strftime('%s', 'now'), strftime('%s', 'now')) ON CONFLICT (title, artist) DO UPDATE SET data = :data, source = :source, mtime = strftime('%s', 'now') ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":title", $this->title),
            new \aportela\DatabaseWrapper\Param\StringParam(":artist", $this->artist),
            new \aportela\DatabaseWrapper\Param\StringParam(":data", $this->lyrics),
            new \aportela\DatabaseWrapper\Param\StringParam(":source", $this->source)
        );
        $dbh->exec($query, $params);
    }

    public function get(\aportela\DatabaseWrapper\DB $dbh, string $title, string $artist): bool
    {
        $this->title = $this->parseTitle($title);
        $this->artist = $this->parseArtist($artist);
        if (!empty($this->title)) {
            if (!empty($this->artist)) {
                $query = " SELECT data, source FROM LYRICS WHERE title = :title AND artist = :artist ";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":title", $this->title),
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist", $this->artist)
                );
                $results = $dbh->query($query, $params);
                if (count($results) == 1) {
                    $this->lyrics = $results[0]->data;
                    $this->source = $results[0]->source;
                    return (true);
                } else {
                    if ($this->scrap($this->title, $this->artist)) {
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
