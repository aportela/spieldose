<?php

    namespace Spieldose\MusicBrainz;

    class Track
    {
        const API_SEARCH_URL = "http://ws.audioscrobbler.com/2.0/?method=track.search&api_key=%s&track=%s&artist=%s&limit=%d&format=json";
        const API_GET_URL_FROM_MBID = "http://ws.audioscrobbler.com/2.0/?method=track.getInfo&api_key=%s&mbid=%s&format=json";
        const API_GET_URL_FROM_TRACK_AND_ARTIST = "http://ws.audioscrobbler.com/2.0/?method=track.getInfo&api_key=%s&track=%s&artist=%s&autocorrect=1&format=json";
        public $mbId;
        public $name;
        public $artistMBId;
        public $artistName;
        public $json;

	    public function __construct (string $mbId = "", string $name = "", string $artistMBId = "", string $artistName = "", string $json = "") {
            $this->mbId = $mbId;
            $this->name = $name;
            $this->artistMBId = $artistMBId;
            $this->artistName = $artistName;
            $this->json = $json;
        }

        public function __destruct() { }

        public static function search(string $name = "", string $artist = "", int $limit = 1): array {
            $results = array();
            $url = sprintf(self::API_SEARCH_URL, \Spieldose\LastFM::API_KEY, $name, $artist, $limit);
            $result = \Spieldose\Net::httpRequest($url);
            $result = json_decode($result);
            if (isset($result->results->trackmatches->track) && is_array($result->results->trackmatches->track)) {
                foreach ($result->results->trackmatches->track as $matchedTrack) {
                    $results[] = new \Spieldose\MusicBrainz\Track($matchedTrack->mbid, $matchedTrack->name, "", $matchedTrack->artist, "");
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
                return(new \Spieldose\MusicBrainz\Track($result->track->mbid, $result->track->name, $result->track->artist->mbid, $result->track->artist->name, $json));
            }
        }

        public static function getFromTrackAndArtist(string $track = "", string $artist = "") {
            if (empty($track)) {
                throw new \Spieldose\Exception\InvalidParamsException("track");
            }
            else if (empty($artist)) {
                throw new \Spieldose\Exception\InvalidParamsException("artist");
            } else {
                $url = sprintf(self::API_GET_URL_FROM_TRACK_AND_ARTIST, \Spieldose\LastFM::API_KEY, $track, $artist);
                $json = \Spieldose\Net::httpRequest($url);
                $result = json_decode($json, false);
                return(new \Spieldose\MusicBrainz\Track($result->track->mbid, $result->track->name, $result->track->artist->mbid, $result->track->artist->name, $json));
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
            $params[] = (new \Spieldose\DatabaseParam())->str(":artist_mbid", $this->artistMBId);
            $params[] = (new \Spieldose\DatabaseParam())->str(":artist_mbname", $this->artistName);
            $params[] = (new \Spieldose\DatabaseParam())->str(":json", $this->json);
            $dbh->execute("REPLACE INTO MB_CACHE_TRACK (mbid, name, artist_mbid, artist_mbname, json) VALUES (:mbid, :name, :artist_mbid, :artist_mbname, :json)", $params);
        }
    }
?>