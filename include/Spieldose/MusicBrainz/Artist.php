<?php

    namespace Spieldose\MusicBrainz;

    class Artist
    {
        const API_SEARCH_URL = "http://ws.audioscrobbler.com/2.0/?method=artist.search&api_key=%s&artist=%s&limit=%d&format=json";
        const API_GET_URL_FROM_MBID = "http://ws.audioscrobbler.com/2.0/?method=artist.getInfo&api_key=%s&mbid=%s&format=json";
        const API_GET_URL_FROM_NAME = "http://ws.audioscrobbler.com/2.0/?method=artist.getInfo&api_key=%s&artist=%s&autocorrect=1&format=json";
        public $mbId;
        public $name;
        public $bio;

	    public function __construct (string $mbId = "", string $name = "", string $bio = "") {
            $this->mbId = $mbId;
            $this->name = $name;
            $this->bio = $bio;
        }

        public function __destruct() { }

        public static function search(string $name = "", int $limit = 1): array {
            $results = array();
            $url = sprintf(self::API_SEARCH_URL, \Spieldose\LastFM::API_KEY, $name, $limit);
            $result = \Spieldose\Net::httpRequest($url);
            $result = json_decode($result);
            if (isset($result->results->artistmatches->artist) && is_array($result->results->artistmatches->artist)) {
                foreach ($result->results->artistmatches->artist as $matchedArtist) {
                    $results[] = new \Spieldose\MusicBrainz\Artist($matchedArtist->mbid, $matchedArtist->name, "");
                }
            }
            return($results);
        }

        public static function getFromMBId(string $mbId = "") {
            if (empty($mbId)) {
                throw new \Spieldose\Exception\InvalidParamsException("mbId");
            } else {
                $url = sprintf(self::API_GET_URL_FROM_MBID, \Spieldose\LastFM::API_KEY, $mbId);
                $result = json_decode(\Spieldose\Net::httpRequest($url), false);
                return(new \Spieldose\MusicBrainz\Artist($result->artist->mbid, $result->artist->name, $result->artist->bio->content));
            }
        }

        public static function getFromArtist(string $artist) {
            if (empty($artist)) {
                throw new \Spieldose\Exception\InvalidParamsException("artist");
            } else {
                $url = sprintf(self::API_GET_URL_FROM_NAME, \Spieldose\LastFM::API_KEY, $artist);
                $result = json_decode(\Spieldose\Net::httpRequest($url), false);
                return(new \Spieldose\MusicBrainz\Artist($result->artist->mbid, $result->artist->name, $result->artist->bio->content));
            }
        }

        public function save(\Spieldose\Database $dbh = null) {
            if (empty($this->mbId)) {
                throw new \Spieldose\Exception\InvalidParamsException("mbId");
            }
            if (empty($this->name)) {
                throw new \Spieldose\Exception\InvalidParamsException("name");
            }
            if (! $dbh) {
                $dbh = new \Spieldose\Database();
            }
            $params[] = (new \Spieldose\DatabaseParam())->str(":mbid", $this->mbId);
            $params[] = (new \Spieldose\DatabaseParam())->str(":name", $this->name);
            if (! empty($this->bio)) {
                $params[] = (new \Spieldose\DatabaseParam())->str(":bio", $this->bio);
            } else {
                $params[] = (new \Spieldose\DatabaseParam())->null(":bio");
            }
            $dbh->execute("REPLACE INTO MB_CACHE_ARTIST (mbid, name, bio) VALUES (:mbid, :name, :bio)", $params);
        }
    }
?>