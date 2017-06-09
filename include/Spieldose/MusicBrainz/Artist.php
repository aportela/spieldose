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
        public $json;

	    public function __construct (string $mbId = "", string $name = "", string $bio = "", string $image = "", string $json = "") {
            $this->mbId = $mbId;
            $this->name = $name;
            $this->bio = $bio;
            $this->image = $image;
            $this->json = $json;
        }

        public function __destruct() { }

        public static function search(string $name = "", int $limit = 1): array {
            $results = array();
            $url = sprintf(self::API_SEARCH_URL, \Spieldose\LastFM::API_KEY, $name, $limit);
            $result = \Spieldose\Net::httpRequest($url);
            $result = json_decode($result);
            if (isset($result->results->artistmatches->artist) && is_array($result->results->artistmatches->artist)) {
                foreach ($result->results->artistmatches->artist as $matchedArtist) {
                    $results[] = new \Spieldose\MusicBrainz\Artist($matchedArtist->mbid, $matchedArtist->name, "", "");
                }
            }
            return($results);
        }

        public static function getFromMBId(string $mbId = "") {
            if (empty($mbId)) {
                throw new \Spieldose\Exception\InvalidParamsException("mbId");
            } else {
                $url = sprintf(self::API_GET_URL_FROM_MBID, \Spieldose\LastFM::API_KEY, $mbId);
                $json = \Spieldose\Net::httpRequest($url);
                $result = json_decode($json, false);
                return(new \Spieldose\MusicBrainz\Artist($result->artist->mbid, $result->artist->name, $result->artist->bio->content, $json));
            }
        }

        private static function getBestImage($imageArray) {
            $images = array_reverse($imageArray);
            foreach($images as $image) {
                if (isset($image->size)) {
                    return($image->{"#text"});
                }
            }
            return($images[0]->{"#text"});
        }

        public static function getFromArtist(string $artist) {
            if (empty($artist)) {
                throw new \Spieldose\Exception\InvalidParamsException("artist");
            } else {
                $url = sprintf(self::API_GET_URL_FROM_NAME, \Spieldose\LastFM::API_KEY, $artist);
                $json = \Spieldose\Net::httpRequest($url);
                $result = json_decode($json, false);
                $image = isset($result->artist->image) ? self::getBestImage($result->artist->image) : "";
                return(new \Spieldose\MusicBrainz\Artist(isset($result->artist->mbid) ? $result->artist->mbid: "", isset($result->artist->name) ? $result->artist->name: "", isset($result->artist->bio->content) ? $result->artist->bio->content: "", $image, $json));
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
            $params[] = (new \Spieldose\DatabaseParam())->str(":artist", $this->name);
            if (! empty($this->bio)) {
                $params[] = (new \Spieldose\DatabaseParam())->str(":bio", $this->bio);
            } else {
                $params[] = (new \Spieldose\DatabaseParam())->null(":bio");
            }
            if (! empty($this->image)) {
                $params[] = (new \Spieldose\DatabaseParam())->str(":image", $this->image);
            } else {
                $params[] = (new \Spieldose\DatabaseParam())->null(":image");
            }
            $params[] = (new \Spieldose\DatabaseParam())->str(":json", $this->json);
            $dbh->execute("REPLACE INTO MB_CACHE_ARTIST (mbid, artist, bio, image, json) VALUES (:mbid, :artist, :bio, :image, :json)", $params);
        }
    }
?>