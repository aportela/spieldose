<?php

    namespace Spieldose\MusicBrainz;

    class Album
    {
        const API_SEARCH_URL = "http://ws.audioscrobbler.com/2.0/?method=album.search&api_key=%s&album=%s&limit=%d&format=json";
        const API_GET_URL_FROM_MBID = "http://ws.audioscrobbler.com/2.0/?method=album.getInfo&api_key=%s&mbid=%s&format=json";
        const API_GET_URL_FROM_ALBUM_AND_ARTIST = "http://ws.audioscrobbler.com/2.0/?method=album.getInfo&api_key=%s&album=%s&artist=%s&autocorrect=1&format=json";
        public $mbId;
        public $name;
        public $artist;
        public $json;

	    public function __construct (string $mbId = "", string $name = "", string $artist = "", string $json = "") {
            $this->mbId = $mbId;
            $this->name = $name;
            $this->artist = $artist;
            $this->json = $json;
        }

        public function __destruct() { }

        public static function search(string $name = "", int $limit = 1): array {
            $results = array();
            $url = sprintf(self::API_SEARCH_URL, \Spieldose\LastFM::API_KEY, $name, $limit);
            $result = \Spieldose\Net::httpRequest($url);
            $result = json_decode($result);
            if (isset($result->results->albummatches->album) && is_array($result->results->albummatches->album)) {
                foreach ($result->results->albummatches->album as $matchedAlbum) {
                    $results[] = new \Spieldose\MusicBrainz\Album($matchedAlbum->mbid, $matchedAlbum->name, $matchedAlbum->artist, "");
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
                return(new \Spieldose\MusicBrainz\Album($result->album->mbid, $result->album->name, $result->album->artist, $json));
            }
        }

        public static function getFromAlbumAndArtist(string $album = "", string $artist = "") {
            if (empty($album)) {
                throw new \Spieldose\Exception\InvalidParamsException("album");
            }
            else if (empty($artist)) {
                throw new \Spieldose\Exception\InvalidParamsException("artist");
            } else {
                $url = sprintf(self::API_GET_URL_FROM_ALBUM_AND_ARTIST, \Spieldose\LastFM::API_KEY, $album, $artist);
                $json = \Spieldose\Net::httpRequest($url);
                $result = json_decode($json, false);
                return(new \Spieldose\MusicBrainz\Album($result->album->mbid, $result->album->name, $result->album->artist, $json));
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
            $params[] = (new \Spieldose\DatabaseParam())->str(":artist", $this->artist);
            $params[] = (new \Spieldose\DatabaseParam())->str(":json", $this->json);
            $dbh->execute("REPLACE INTO MB_CACHE_ALBUM (mbid, name, artist, json) VALUES (:mbid, :name, :artist, :json)", $params);
        }
    }
?>