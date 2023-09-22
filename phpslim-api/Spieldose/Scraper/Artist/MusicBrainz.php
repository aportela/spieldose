<?php

declare(strict_types=1);

namespace Spieldose\Scraper\Artist;

class MusicBrainz
{

    private \Psr\Log\LoggerInterface $logger;
    private \aportela\MusicBrainzWrapper\APIFormat $apiFormat;
    private bool $scraped;

    public string $mbId;
    public ?string $name;
    public ?string $image;
    public ?string $country;

    public array $genres;
    public array $relations;

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

    public function getMBIdFromName(string $name): bool
    {
        $mbArtist = new \aportela\MusicBrainzWrapper\Artist($this->logger, $this->apiFormat);
        $results = $mbArtist->search($name, 1);
        if (count($results) == 1 && !empty($results[0]->mbId)) {
            $this->mbId = $results[0]->mbId;
            return (true);
        } else {
            return (false);
        }
    }

    public function scrap(): bool
    {
        $this->scraped = false;
        if (empty($this->mbId)) {
            if (!empty($this->name)) {
                try {
                    if (!$this->getMBIdFromName(($this->name))) {
                        $this->logger->warning(sprintf("[MusicBrainz] mbid not found for name: %s", $this->name));
                    }
                } catch (\Throwable $e) {
                    $this->logger->warning(sprintf("[MusicBrainz] error getting mbid from name %s: %s", $this->name, $e->getMessage()));
                }
            } else {
                $this->logger->warning("[MusicBrainz] mbid,name not found");
            }
        }
        if (!empty($this->mbId)) {
            $mbArtist = new \aportela\MusicBrainzWrapper\Artist($this->logger, $this->apiFormat);
            try {
                $mbArtist->get($this->mbId);
                $this->name = $mbArtist->name;
                $this->country = $mbArtist->country;
                $this->genres = $mbArtist->genres ?? [];
                $this->relations = $mbArtist->relations ?? [];
                $artistImageURLs = $mbArtist->getURLRelationshipValues(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType::IMAGE);
                if (count($artistImageURLs) > 0) {
                    if (str_starts_with($artistImageURLs[0], "https://commons.wikimedia.org/wiki/File:")) {
                        $fields = explode("/File:", $artistImageURLs[0]);
                        if (count($fields) == 2) {
                            $f = new \aportela\MediaWikiWrapper\Wikipedia\File($this->logger, \aportela\MediaWikiWrapper\APIType::REST);
                            $f->setTitle($fields[1]);
                            try {
                                $f->get();
                                $this->image = $f->getURL(\aportela\MediaWikiWrapper\FileInformationType::ORIGINAL);
                            } catch (\Throwable $e) {
                                $this->logger->warning(sprintf("[MusicBrainz] error getting image from musicbrainz wikipedia relation: %s", $artistImageURLs[0]));
                            }
                        }
                    } else {
                        $this->image = $artistImageURLs[0];
                    }
                }
                $this->scraped = true;
            } catch (\Throwable $e) {
                $this->logger->warning(sprintf("[MusicBrainz] error scraping mbid %s: %s", $this->mbId, $e->getMessage()));
            }
        }
        return ($this->scraped);
    }

    public function fixTags(\aportela\DatabaseWrapper\DB $dbh): bool
    {
        if (!empty($this->mbId) && !empty($this->name)) {
            $query = " UPDATE FILE_ID3_TAG SET mb_artist_id = :mbid WHERE mb_artist_id IS NULL AND artist = :artist ";
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId),
                new \aportela\DatabaseWrapper\Param\StringParam(":artist", $this->name),
            );
            $totalUpdates = $dbh->exec($query, $params);
            $query = " UPDATE FILE_ID3_TAG SET mb_album_artist_id = :mbid WHERE mb_album_artist_id IS NULL AND album_artist = :artist ";
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId),
                new \aportela\DatabaseWrapper\Param\StringParam(":artist", $this->name),
            );
            $totalUpdates += $dbh->exec($query, $params);
            return ($totalUpdates > 0);
        } else {
            return (false);
        }
    }

    public function saveCache(\aportela\DatabaseWrapper\DB $dbh)
    {
        $query = "
            INSERT INTO CACHE_ARTIST_MUSICBRAINZ (mbid, name, image, country, ctime, mtime) VALUES (:mbid, :name, :image, :country, strftime('%s', 'now'), strftime('%s', 'now'))
                ON CONFLICT(mbid) DO
            UPDATE SET name = :name, image = :image, country = :country, mtime = strftime('%s', 'now')
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId),
            new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name)
        );
        if (!empty($this->image)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":image", $this->image);
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":image");
        }
        if (!empty($this->country)) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":country", mb_strtolower($this->country));
        } else {
            $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":country");
        }
        $dbh->exec($query, $params);
        $query = "
            DELETE FROM CACHE_ARTIST_MUSICBRAINZ_GENRE WHERE artist_mbid = :artist_mbid
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $this->mbId)
        );
        $dbh->exec($query, $params);
        if (is_array($this->genres) && count($this->genres) > 0) {
            foreach ($this->genres as $genre) {
                $query = "
                    INSERT INTO CACHE_ARTIST_MUSICBRAINZ_GENRE (artist_mbid, genre) VALUES (:artist_mbid, :genre)
                ";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $this->mbId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":genre", mb_strtolower($genre))
                );
                $dbh->exec($query, $params);
            }
        }
        $query = "
            DELETE FROM CACHE_ARTIST_MUSICBRAINZ_URL_RELATIONSHIP WHERE artist_mbid = :artist_mbid
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $this->mbId)
        );
        $dbh->exec($query, $params);
        $allowedRelations = array_column(\aportela\MusicBrainzWrapper\ArtistURLRelationshipType::cases(), 'value');
        if (is_array($this->relations) && count($this->relations) > 0) {
            foreach ($this->relations as $relation) {
                if (in_array($relation->typeId, $allowedRelations)) {
                    $query = "
                        INSERT INTO CACHE_ARTIST_MUSICBRAINZ_URL_RELATIONSHIP (artist_mbid, relation_type_id, name, url) VALUES (:artist_mbid, :relation_type_id, :name, :url)
                            ON CONFLICT(artist_mbid, relation_type_id, url) DO
                        UPDATE SET name = :name, url = :url
                    ";
                    $params = array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $this->mbId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":relation_type_id", $relation->typeId),
                        new \aportela\DatabaseWrapper\Param\StringParam(":name", $relation->name),
                        new \aportela\DatabaseWrapper\Param\StringParam(":url", $relation->url)
                    );
                    $dbh->exec($query, $params);
                }
            }
        }
    }
}
