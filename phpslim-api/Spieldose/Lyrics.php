<?php

declare(strict_types=1);

namespace Spieldose;

class Lyrics
{
    public string $hash;
    public string $title;
    public string $artist;
    public ?string $data;
    public string $source;

    public function __construct(string $title, string $artist)
    {
        $this->title = trim($title);
        // ugly hack to scrap "live versions"
        $this->title = preg_replace("/ \(live\)$/i", "", $this->title);
        $this->artist = trim($artist);
        $this->hash = md5($this->title . $this->artist);
        $this->data = null;
    }

    public function __destruct()
    {
    }

    private function scrapFromGoogle(): bool
    {
        // (AT THIS TIME) this is REQUIRED/IMPORTANT, with another user agents the search response is not the same (do not include lyrics!)
        $userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36 Edg/116.0.1938.81";
        $request = new \aportela\HTTPRequestWrapper\HTTPRequest(new \Psr\Log\NullLogger(), $userAgent);
        $response = $request->GET(sprintf("https://www.google.com/search?client=firefox-b-d&%s", http_build_query(["q" => sprintf("lyrics \"%s\" from \"%s\"", $this->title, $this->artist)])));
        if ($response->code == 200 && !empty($response->body)) {
            libxml_use_internal_errors(true);
            $doc = new \DomDocument();
            if ($doc->loadHTML($response->body)) {
                $xpath = new \DOMXPath($doc);
                // lyric paragraphs are contained on a <div jsname="WbKHeb"> with <span> childs
                $nodes = $xpath->query('//div[@jsname="WbKHeb"]//span');
                if ($nodes != false) {
                    if ($nodes->count() > 0) {
                        $data = null;
                        foreach ($nodes as $key => $node) {
                            $data .= trim($node->textContent) . PHP_EOL;
                        }
                        if (!empty($data)) {
                            $this->source = !empty($data) ? "google" : null;
                            $this->data = $data;
                            return (true);
                        } else {
                            return (false);
                        }
                    } else {
                        throw new \Spieldose\Exception\NotFoundException('//div[@jsname="WbKHeb"]//span');
                    }
                } else {
                    throw new \Spieldose\Exception\NotFoundException('//div[@jsname="WbKHeb"]//span');
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("body");
            }
        } else {
            throw new \Exception("Invalid HTTP response code: " . $response->code);
        }
    }

    private function scrapFromBing(): bool
    {
        $request = new \aportela\HTTPRequestWrapper\HTTPRequest(new \Psr\Log\NullLogger());
        $response = $request->GET(sprintf("https://www.bing.com/search?%s", http_build_query(["q" => sprintf("lyrics \"%s\" from \"%s\"", $this->title, $this->artist)])));
        if ($response->code == 200 && !empty($response->body)) {
            libxml_use_internal_errors(true);
            $doc = new \DomDocument();
            if ($doc->loadHTML(str_ireplace(array("<br>", "<br/>", "<br />"), PHP_EOL, $response->body))) {
                $xpath = new \DOMXPath($doc);
                // lyric paragraphs are contained on a <div class="lyrics"> with <span> childs
                $nodes = $xpath->query('//div[@class="lyrics"]//div');
                if ($nodes != false) {
                    if ($nodes->count() > 0) {
                        $data = null;
                        foreach ($nodes as $key => $node) {
                            $data .= trim($node->textContent) . PHP_EOL;
                        }
                        if (!empty($data)) {
                            $this->source = !empty($data) ? "bing" : null;
                            $this->data = $data;
                            return (true);
                        } else {
                            return (false);
                        }
                    } else {
                        throw new \Spieldose\Exception\NotFoundException('//div[@jsname="lyrics"]//div');
                    }
                } else {
                    throw new \Spieldose\Exception\NotFoundException('//div[@jsname="lyrics"]//div');
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("body");
            }
        } else {
            throw new \Exception("Invalid HTTP response code: " . $response->code);
        }
    }

    private function scrapFromDuckDuckGo(): bool
    {
        $request = new \aportela\HTTPRequestWrapper\HTTPRequest(new \Psr\Log\NullLogger(), "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/117.0");
        $response = $request->GET(sprintf("https://duckduckgo.com/a.js?s=lyrics&from=lyrics&%s", http_build_query(["ta" => $this->artist, "tl" => $this->title])));
        if ($response->code == 200 && !empty($response->body)) {
            $pattern = '/DDG.duckbar.add_array\(\[\{"data":\[\{"Abstract":"(.*)","AbstractSource":"Musixmatch"/';
            if (preg_match($pattern, $response->body, $match)) {
                if (count($match) == 2) {
                    $data = null;
                    foreach (explode(PHP_EOL, str_ireplace(array("<br>", "<br/>", "<br />"), PHP_EOL, $match[1])) as $line) {
                        $data .= trim($line) . PHP_EOL;
                    };
                    if (!empty($data)) {
                        $this->source = !empty($data) ? "bing" : null;
                        $this->data = $data;
                        return (true);
                    } else {
                        return (false);
                    }
                } else {
                    throw new \Spieldose\Exception\NotFoundException('DDG.duckbar.add_array');
                }
            } else {
                throw new \Spieldose\Exception\NotFoundException('DDG.duckbar.add_array');
            }
        } else {
            throw new \Exception("Invalid HTTP response code: " . $response->code);
        }
    }

    private function scrap(\aportela\DatabaseWrapper\DB $dbh): bool
    {
        if (!empty($this->title)) {
            if (mb_strlen($this->title) > 512) {
                throw new \Spieldose\Exception\InvalidParamsException("title length");
            }
            if (!empty($this->artist)) {
                if (mb_strlen($this->artist) > 128) {
                    throw new \Spieldose\Exception\InvalidParamsException("artist length");
                }
                try {
                    if (!$this->scrapFromGoogle()) {
                        if (!$this->scrapFromDuckDuckGo()) {
                            $this->scrapFromBing();
                        }
                    }
                } catch (\Throwable $e) {
                    // TODO
                }
                if (!empty($this->data)) {
                    $this->data = trim($this->data);
                    $query = " INSERT INTO LYRICS (md5_hash, title, artist, data, source, ctime, mtime) VALUES (:md5_hash, :title, :artist, :data, :source, strftime('%s', 'now'), strftime('%s', 'now')) ON CONFLICT (md5_hash) DO UPDATE SET data = :data, source = :source, mtime = strftime('%s', 'now') ";
                    $params = array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":md5_hash", $this->hash),
                        new \aportela\DatabaseWrapper\Param\StringParam(":title", $this->title),
                        new \aportela\DatabaseWrapper\Param\StringParam(":artist", $this->artist),
                        new \aportela\DatabaseWrapper\Param\StringParam(":data", $this->data),
                        new \aportela\DatabaseWrapper\Param\StringParam(":source", $this->source)
                    );
                    $dbh->exec($query, $params);
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

    public function get(\aportela\DatabaseWrapper\DB $dbh): bool
    {
        if (!empty($this->title)) {
            if (!empty($this->artist)) {
                $query = " SELECT data FROM LYRICS WHERE md5_hash = :md5_hash ";
                $params = array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":md5_hash", $this->hash)
                );
                $results = $dbh->query($query, $params);
                if (count($results) == 1) {
                    $this->data = $results[0]->data;
                    return (true);
                } else {
                    return ($this->scrap($dbh));
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("artist");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("title");
        }
    }
}
