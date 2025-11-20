<?php

declare(strict_types=1);

namespace Spieldose\Entities;

use stdClass;

class File
{
    protected \aportela\DatabaseWrapper\DB $dbh;

    public string $id;
    public string $filename;
    public int $filesize;
    public string $mime;
    public stdClass $trackInfo;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function get(\aportela\DatabaseWrapper\DB $dbh)
    {
        $results = $dbh->query(
            "
                SELECT
                    FILE.name, FILE.size, FILE_ID3_TAG.mime, FILE_ID3_TAG.title, FILE_ID3_TAG.playtime_seconds, FILE_ID3_TAG.release_track_mbid, FILE_ID3_TAG.artist, FILE_ID3_TAG.album, COALESCE(FILE_ID3_TAG.original_year, FILE_ID3_TAG.year) AS year
                FROM FILE
                LEFT JOIN FILE_ID3_TAG ON FILE_ID3_TAG.file_id = FILE.id
                WHERE FILE.id = :id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id)
            ]
        );
        if (count($results) == 1) {
            $this->filename = $results[0]->name;
            $this->filesize = $results[0]->size;
            $this->mime = $results[0]->mime;
            $this->trackInfo = new \stdClass();
            $this->trackInfo->playTimeSeconds = $results[0]->playtime_seconds;
            $this->trackInfo->title = $results[0]->title;
            $this->trackInfo->artist = $results[0]->artist;
            $this->trackInfo->album = $results[0]->album;
            $this->trackInfo->year = intval($results[0]->year);
        } else {
            throw new \Spieldose\Exception\NotFoundException("id");
        }
    }

    /**
     * temporal method
     */
    public function rnd(\aportela\DatabaseWrapper\DB $dbh)
    {
        $results = $dbh->query(
            "
                SELECT
                    FILE.id
                FROM FILE
                ORDER BY RANDOM()
            "
        );
        $this->id = $results[0]->id;
    }
}
