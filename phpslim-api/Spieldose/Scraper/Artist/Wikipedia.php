<?php

declare(strict_types=1);

namespace Spieldose\Scraper\Artist;

class Wikipedia
{
    private \Psr\Log\LoggerInterface $logger;
    private bool $scraped;

    public ?string $mbId;
    public string $name;
    public string $url;
    public string $language;
    public string $html;

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->scraped = false;
        $this->mbId = null;
        $this->language =  \aportela\MediaWikiWrapper\Language::ENGLISH->value;
    }

    public function isScraped(): bool
    {
        return ($this->scraped);
    }

    public function scrapWikipedia(string $url): bool
    {
        $this->scraped = false;
        try {
            $wikiPage = new \aportela\MediaWikiWrapper\Wikipedia\Page($this->logger, \aportela\MediaWikiWrapper\APIType::REST);
            $wikiPage->setURL($url);
            $this->html = $wikiPage->getHTML();
            $this->url = $url;
            $this->scraped = true;
            return ($this->scraped);
        } catch (\Throwable $e) {
            $this->logger->error(sprintf("[Wikipedia] error scraping from url %s: %s", $url, $e->getMessage()));
            return (false);
        }
    }

    public function scrapWikidata(string $url): bool
    {
        $this->scraped = false;
        try {
            $item = new \aportela\MediaWikiWrapper\Wikidata\Item($this->logger, \aportela\MediaWikiWrapper\APIType::REST);
            $item->setURL($url);
            $title = $item->getWikipediaTitle(\aportela\MediaWikiWrapper\Language::ENGLISH);
            if (!empty($title)) {
                $wikiPage = new \aportela\MediaWikiWrapper\Wikipedia\Page($this->logger, \aportela\MediaWikiWrapper\APIType::REST);
                $wikiPage->setTitle($title);
                $this->url = sprintf(\aportela\MediaWikiWrapper\Wikipedia\Page::REST_API_PAGE_HTML, $title);
                $this->html = $wikiPage->getHTML();
                $this->scraped = !empty($this->html);
                return ($this->scraped);
            } else {
                $this->logger->warning(sprintf("[Wikidata] error getting wikipedia page title from url %s", $url));
            }
        } catch (\Throwable $e) {
            $this->logger->error(sprintf("[Wikidata] error scraping from url %s: %s", $url, $e->getMessage()));
            return (false);
        }
    }

    public function saveCache(\aportela\DatabaseWrapper\DB $dbh): bool
    {
        if (empty($this->name)) {
            throw new \Spieldose\Exception\InvalidParamsException("name");
        } else if (empty($this->url)) {
            throw new \Spieldose\Exception\InvalidParamsException("url");
        } else if (empty($this->language)) {
            throw new \Spieldose\Exception\InvalidParamsException("language");
        } else if (empty($this->html)) {
            throw new \Spieldose\Exception\InvalidParamsException("html");
        } else {
            $query = "
                INSERT INTO CACHE_ARTIST_WIKIPEDIA (mbid, name, url, language, extract, html_page, ctime, mtime) VALUES (:mbid, :name, :url, :language, :html, :html, strftime('%s', 'now'), strftime('%s', 'now'))
                    ON CONFLICT(name, url, language) DO
                UPDATE SET extract = :html, html_page = :html, mtime = strftime('%s', 'now')
            ";
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $this->name),
                new \aportela\DatabaseWrapper\Param\StringParam(":url", $this->url),
                new \aportela\DatabaseWrapper\Param\StringParam(":language", $this->language),
                new \aportela\DatabaseWrapper\Param\StringParam(":html", $this->html),
            );
            if (!empty($this->mbId)) {
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId);
            } else {
                $params[] = new \aportela\DatabaseWrapper\Param\NullParam(":mbid");
            }
            $dbh->exec($query, $params);
            return (true);
        }
    }
}
