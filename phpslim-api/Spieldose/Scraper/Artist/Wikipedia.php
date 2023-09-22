<?php

declare(strict_types=1);

namespace Spieldose\Scraper\Artist;

class Wikipedia
{
    private \Psr\Log\LoggerInterface $logger;
    private bool $scraped;

    public string $name;
    public string $url;
    public string $language;
    public string $html;

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->scraped = false;
        $this->url = "";
        $this->language =  \aportela\MediaWikiWrapper\Language::ENGLISH->value;
    }

    public function isScraped(): bool
    {
        return ($this->scraped);
    }

    public function scrapWikipedia(): bool
    {
        $this->scraped = false;
        if (!empty($this->url)) {
            try {
                $wikiPage = new \aportela\MediaWikiWrapper\Wikipedia\Page($this->logger, \aportela\MediaWikiWrapper\APIType::REST);
                $wikiPage->setURL($this->url);
                $this->html = $wikiPage->getHTML();
                $this->scraped = true;
            } catch (\Throwable $e) {
                $this->logger->warning(sprintf("[Wikipedia] error scraping url %s: %s", $this->url, $e->getMessage()));
            }
        }
        return ($this->scraped);
    }

    public function scrapWikidata(): bool
    {
        $this->scraped = false;
        if (!empty($this->url)) {
            try {
                $item = new \aportela\MediaWikiWrapper\Wikidata\Item($this->logger, \aportela\MediaWikiWrapper\APIType::REST);
                $item->setURL($this->url);
                $title = $item->getWikipediaTitle(\aportela\MediaWikiWrapper\Language::ENGLISH);
                if (!empty($title)) {
                    $wikiPage = new \aportela\MediaWikiWrapper\Wikipedia\Page($this->logger, \aportela\MediaWikiWrapper\APIType::REST);
                    $wikiPage->setTitle($title);
                    $this->html = $wikiPage->getHTML();
                    $this->scraped = !empty($this->html);
                } else {
                    $this->logger->warning(sprintf("[Wikidata] error getting title from url %s", $this->url));
                }
            } catch (\Throwable $e) {
                $this->logger->warning(sprintf("[Wikidata] error scraping url %s: %s", $this->url, $e->getMessage()));
            }
        }
        return ($this->scraped);
    }

    public function saveCache(\aportela\DatabaseWrapper\DB $dbh)
    {
        $query = "
            INSERT INTO CACHE_ARTIST_WIKIPEDIA (name, url, language, html, ctime, mtime) VALUES (:name, :url, :language, :html, strftime('%s', 'now'), strftime('%s', 'now'))
                ON CONFLICT(name, url, language) DO
            UPDATE SET html = :html, mtime = strftime('%s', 'now')
        ";
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name ?? ""),
            new \aportela\DatabaseWrapper\Param\StringParam(":url", $this->url ?? ""),
            new \aportela\DatabaseWrapper\Param\StringParam(":language", $this->language),
            new \aportela\DatabaseWrapper\Param\StringParam(":html", $this->html),
        );
        $dbh->exec($query, $params);
    }
}
