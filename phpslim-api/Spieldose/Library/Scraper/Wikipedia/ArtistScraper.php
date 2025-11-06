<?php

declare(strict_types=1);

namespace Spieldose\Library\Scraper\Wikipedia;

class ArtistScraper
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;
    private \aportela\MediaWikiWrapper\Wikidata\Item $wikidataArtistAPI;
    private \aportela\MediaWikiWrapper\Wikipedia\Page $wikipediaArtistAPI;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger, \aportela\SimpleFSCache\Cache $cache)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->wikidataArtistAPI = new \aportela\MediaWikiWrapper\Wikidata\Item($logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, $cache);
        $this->wikipediaArtistAPI = new \aportela\MediaWikiWrapper\Wikipedia\Page($logger, \aportela\MediaWikiWrapper\API::DEFAULT_THROTTLE_DELAY_MS, $cache);
        libxml_use_internal_errors(true);
    }

    public function __destruct() {}

    /**
     * @return array<\stdClass>
     */
    private function getArtistsData(bool $withoutCache = true): array
    {
        $data = [];
        $withoutCacheWhereCondition = "
            LEFT JOIN CACHE_ARTIST_WIKIPEDIA ON CACHE_ARTIST_WIKIPEDIA.artist_mbid = CACHE_MUSICBRAINZ_ARTIST.mbid
            WHERE
                CACHE_ARTIST_WIKIPEDIA.artist_mbid IS NULL
        ";
        $results = $this->dbh->query(
            sprintf(
                "
                    SELECT
                        CACHE_MUSICBRAINZ_ARTIST.mbid, CACHE_MUSICBRAINZ_ARTIST.name, CACHE_MUSICBRAINZ_ARTIST_URL_RELATIONSHIP.url
                    FROM CACHE_MUSICBRAINZ_ARTIST
                    LEFT JOIN CACHE_MUSICBRAINZ_ARTIST_URL_RELATIONSHIP ON (
                        CACHE_MUSICBRAINZ_ARTIST_URL_RELATIONSHIP.artist_mbid = CACHE_MUSICBRAINZ_ARTIST.mbid
                        AND
                        CACHE_MUSICBRAINZ_ARTIST_URL_RELATIONSHIP.relation_type_id = :relation_type_id
                    )
                    %s
                ",
                $withoutCache ? $withoutCacheWhereCondition : null
            ),
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":relation_type_id", \aportela\MusicBrainzWrapper\ArtistURLRelationshipType::DATABASE_WIKIDATA->value)
            ]
        );
        foreach ($results as $result) {
            if (! empty($result->url)) {
                $artist = new \stdClass();
                $artist->mbId = $result->mbid;
                $artist->name = $result->name;
                $artist->WikidataURL = $result->url;
                $data[] = $artist;
            }
        }
        return ($data);
    }

    /**
     * save Wikipedia artist cache (html page)
     */
    private function saveCache(\stdClass $artist, \aportela\MediaWikiWrapper\Language $language, string $html)
    {
        $this->dbh->execute(
            "
                INSERT INTO CACHE_ARTIST_WIKIPEDIA
                    (artist_mbid, artist_name, language, html, ctime, mtime)
                VALUES
                    (:artist_mbid, :artist_name, :language, :html, :current_timestamp, NULL)
                ON CONFLICT (artist_mbid, artist_name, language) DO
                    UPDATE SET
                        html = :html,
                        mtime = :current_timestamp
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $artist->mbId),
                new \aportela\DatabaseWrapper\Param\StringParam(":artist_name", $artist->name),
                new \aportela\DatabaseWrapper\Param\StringParam(":language", $language->value),
                new \aportela\DatabaseWrapper\Param\StringParam(":html", $html),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
            ]
        );
    }

    private function stripHTML(string $html): string|false
    {
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $links = $dom->getElementsByTagName("a");
        foreach ($links as $link) {
            $textNode = $dom->createTextNode($link->textContent);
            $link->parentNode->replaceChild($textNode, $link);
        }
        return ($dom->saveHTML());
    }

    public function scrapMissingCache(?callable $scrapItemCallback = null, bool $force = false): float
    {
        $scanStartTime = microtime(true);
        $artistsData = $this->getArtistsData(!$force);
        $totalArtistsData = count($artistsData);
        for ($i = 0; $i < $totalArtistsData; $i++) {
            if ($scrapItemCallback != null) {
                call_user_func($scrapItemCallback, $artistsData, $totalArtistsData, $i);
            }
            try {
                $language = \aportela\MediaWikiWrapper\Language::ENGLISH;
                if (isset($artistsData[$i]->WikidataURL) && ! empty($artistsData[$i]->WikidataURL)) {
                    $title = $this->wikidataArtistAPI->getWikipediaTitleFromURL($artistsData[$i]->WikidataURL);
                    $html = $this->wikipediaArtistAPI->getHTMLFromTitle($title, $language);
                    //$html = $this->stripHTML($html);
                    $this->saveCache($artistsData[$i], $language, $html);
                }
            } catch (\aportela\MediaWikiWrapper\Exception\NotFoundException $e) {
                $this->logger->warning("Wikipedia artist not found", [$artistsData[$i], $e->getMessage()]);
            } catch (\aportela\MediaWikiWrapper\Exception\RemoteAPIServerConnectionException $e) {
                $this->logger->warning("Wikipedia API server not reachable", [$artistsData[$i], $e->getMessage()]);
            } catch (\Throwable $e) {
                print_r($e);
                exit;
                $this->logger->warning("Wikipedia artist scrap error", [$artistsData[$i], $e->getMessage(), $e->getPrevious()]);
            }
        }
        return (microtime(true) - $scanStartTime);
    }
}
