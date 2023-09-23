<?php

declare(strict_types=1);

namespace Spieldose\Scraper\Release;

class MusicBrainz
{
    private \Psr\Log\LoggerInterface $logger;
    private \aportela\MusicBrainzWrapper\APIFormat $apiFormat;
    private bool $scraped;

    public string $mbId;
    public string $title;
    public ?int $year;
    public array $media;
    public object $artist;

    public function __construct(\Psr\Log\LoggerInterface $logger, \aportela\MusicBrainzWrapper\APIFormat $apiFormat)
    {
        $this->logger = $logger;
        $this->apiFormat = $apiFormat;
        $this->scraped = false;
    }

    public function isScraped(): bool
    {
        return ($this->scraped);
    }

    public function scrap(): bool
    {
        $this->scraped = false;
        if (!empty($this->mbId)) {
            $mbRelease = new \aportela\MusicBrainzWrapper\Release($this->logger, $this->apiFormat);
            try {
                $mbRelease->get($this->mbId);
                $this->title = $mbRelease->title;
                $this->year = $mbRelease->year;
                $this->artist = $mbRelease->artist;
                $this->media = $mbRelease->media;
                $this->scraped = true;
                echo "scrapped";
            } catch (\Throwable $e) {
                echo "not scraped";
                $this->logger->warning(sprintf("[MusicBrainz] error scraping mbid %s: %s", $this->mbId, $e->getMessage()));
            }
        }
        return ($this->scraped);
    }

    public function saveCache(\aportela\DatabaseWrapper\DB $dbh)
    {
        $query = "
            INSERT INTO CACHE_RELEASE_MUSICBRAINZ (mbid, title, year, media_count, ctime, mtime) VALUES (:mbid, :title, :year, :media_count, strftime('%s', 'now'), strftime('%s', 'now'))
                ON CONFLICT(mbid) DO
            UPDATE SET title = :title, year = :year, media_count = :media_count, mtime = strftime('%s', 'now')
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId),
            new \aportela\DatabaseWrapper\Param\StringParam(":title", $this->title),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":media_count", count($this->media))
        );
        if ($this->year != null) {
            $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":year", (int) $this->year);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":year");
        }
        $dbh->exec($query, $params);
        $query = "
            DELETE FROM CACHE_RELEASE_MUSICBRAINZ_MEDIA WHERE release_mbid = :release_mbid
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":release_mbid", $this->mbId)
        );
        $dbh->exec($query, $params);
        $query = "
            DELETE FROM CACHE_RELEASE_MUSICBRAINZ_MEDIA_TRACK WHERE release_mbid = :release_mbid
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":release_mbid", $this->mbId)
        );
        $dbh->exec($query, $params);

        if (is_array($this->media) && count($this->media) > 0) {
            foreach ($this->media as $media) {
                $query = "
                    INSERT INTO CACHE_RELEASE_MUSICBRAINZ_MEDIA (release_mbid, position, track_count) VALUES (:release_mbid, :position, :track_count)
                ";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":release_mbid", $this->mbId),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":position", $media->position),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":track_count", count($media->tracks))
                );
                $dbh->exec($query, $params);
                foreach ($media->tracks as $track) {
                    $query = "
                        INSERT INTO CACHE_RELEASE_MUSICBRAINZ_MEDIA_TRACK (mbid, release_mbid, release_media, position, title, artist_mbid, artist_name) VALUES (:mbid, :release_mbid, :release_media, :position, :title, :artist_mbid, :artist_name)
                    ";
                    $params = array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $track->mbId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":release_mbid", $this->mbId),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":release_media", $media->position),
                        new \aportela\DatabaseWrapper\Param\IntegerParam(":position", $track->position),
                        new \aportela\DatabaseWrapper\Param\StringParam(":title", $track->title),
                        new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $track->artist->mbId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":artist_name", $track->artist->name),
                    );
                    $dbh->exec($query, $params);
                }
            }
        }
    }
}
