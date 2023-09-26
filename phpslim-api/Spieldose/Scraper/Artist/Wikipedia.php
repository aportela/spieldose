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

    private function stripWikipediaHTMLPage(string $html): string
    {
        // strip styles
        //$pattern = '/\<(\w+)\s[^>]*?style=([\"|\']).*?\2\s?[^>]*?(\/?)>/';
        //$html = preg_replace($pattern, "", $html);
        libxml_use_internal_errors(true);
        $doc = new \DomDocument();
        if ($doc->loadHTML($html)) {
            $xpath = new \DOMXPath($doc);
            // lyric paragraphs are contained on a <div jsname="WbKHeb"> with <span> childs
            $nodes = $xpath->query('//section');
            if ($nodes != false) {
                if ($nodes->count() > 0) {
                    $html = null;
                    foreach ($nodes as $node) {
                        $html .= sprintf(
                            "<section>%s</section>",
                            implode(array_map(
                                [$node->ownerDocument, "saveHTML"],
                                iterator_to_array($node->childNodes)
                            ))
                        );
                    }
                    return ($html);
                } else {
                    throw new \Spieldose\Exception\NotFoundException("section");
                }
            } else {
                throw new \Spieldose\Exception\NotFoundException("section");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("html");
        }
    }

    public function scrapWikipedia(string $url): bool
    {
        $this->scraped = false;
        try {
            $wikiPage = new \aportela\MediaWikiWrapper\Wikipedia\Page($this->logger, \aportela\MediaWikiWrapper\APIType::REST);
            $wikiPage->setURL($url);
            $this->intro = \Spieldose\Utils::nl2P($wikiPage->getIntroPlainText(), true);
            $this->html = $this->stripWikipediaHTMLPage($wikiPage->getHTML());
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
                $this->intro = \Spieldose\Utils::nl2P($wikiPage->getIntroPlainText(), true);
                $this->html = $this->stripWikipediaHTMLPage($wikiPage->getHTML());
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
        } elseif (empty($this->intro)) {
            throw new \Spieldose\Exception\InvalidParamsException("intro");
        } elseif (empty($this->html)) {
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
