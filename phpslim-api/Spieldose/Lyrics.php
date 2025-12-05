<?php

declare(strict_types=1);

namespace Spieldose;

class Lyrics extends \aportela\ScraperLyrics\Lyrics
{
    public $title;

    public $artist;

    public $lyrics;

    public $source;

    private function save(\aportela\DatabaseWrapper\DB $db): void
    {
        $db->execute(
            "
                INSERT INTO LYRICS
                    (title, artist, data, source, ctime, mtime)
                VALUES
                    (:title, :artist, :data, :source, :current_timestamp, NULL)
                ON CONFLICT (title, artist)
                DO UPDATE SET
                    data = :data,
                    source = :source,
                    mtime = :current_timestamp
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":title", $this->title),
                new \aportela\DatabaseWrapper\Param\StringParam(":artist", $this->artist),
                new \aportela\DatabaseWrapper\Param\StringParam(":data", $this->lyrics),
                new \aportela\DatabaseWrapper\Param\StringParam(":source", $this->source),
                new \aportela\DatabaseWrapper\Param\StringParam(":current_timestamp", intval(microtime(true) * 1000)),

            ]
        );
    }

    public function get(\aportela\DatabaseWrapper\DB $db, string $title, string $artist): bool
    {
        $this->title = $this->parseTitle($title);
        $this->artist = $this->parseArtist($artist);
        if ($this->title !== '' && $this->title !== '0') {
            if ($this->artist !== '' && $this->artist !== '0') {
                $results = $db->query(
                    "
                        SELECT
                            data, source
                        FROM LYRICS
                        WHERE title = :title
                        AND artist = :artist
                    ",
                    [
                        new \aportela\DatabaseWrapper\Param\StringParam(":title", $this->title),
                        new \aportela\DatabaseWrapper\Param\StringParam(":artist", $this->artist),
                    ]
                );
                if (count($results) === 1) {
                    $this->lyrics = $results[0]->data;
                    $this->source = $results[0]->source;
                    return (true);
                } elseif ($this->scrap($this->title, $this->artist)) {
                    $this->save($db);
                    return (true);
                } else {
                    return (false);
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("artist");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("title");
        }
    }
}
