<?php

declare(strict_types=1);

namespace Spieldose\Scraper\Artist;

class Wikipedia
{
    private \Psr\Log\LoggerInterface $logger;
    private bool $scraped;

    public string $mbId;
    public string $intro;
    public string $html;

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->scraped = false;
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
            $this->intro = $wikiPage->getIntroPlainText();
            $this->html = $wikiPage->getHTML();
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
                $this->intro = $wikiPage->getIntroPlainText();
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
        if (empty($this->mbId)) {
            throw new \Spieldose\Exception\InvalidParamsException("mbId");
        } else if (empty($this->intro)) {
            throw new \Spieldose\Exception\InvalidParamsException("intro");
        } else if (empty($this->html)) {
            throw new \Spieldose\Exception\InvalidParamsException("html");
        } else {
            $query = "
                INSERT INTO CACHE_ARTIST_WIKIPEDIA (mbid, intro, html_page, ctime, mtime) VALUES (:mbid, :intro, :html_page, strftime('%s', 'now'), strftime('%s', 'now'))
                    ON CONFLICT(mbid) DO
                UPDATE SET intro = :intro, html_page = :html_page, mtime = strftime('%s', 'now')
            ";
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId),
                new \aportela\DatabaseWrapper\Param\StringParam(":intro", $this->intro),
                new \aportela\DatabaseWrapper\Param\StringParam(":html_page", $this->html),
            );
            $dbh->exec($query, $params);
            return (true);
        }
    }
}
