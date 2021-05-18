<?php

    declare(strict_types=1);

    namespace Spieldose\Cache;

    class Artist {
        const MUSICBRAINZ_API_SEARCH_ARTIST_FROM_NAME = "http://musicbrainz.org/ws/2/artist/?query=artist:%s&fmt=json";
        const MUSICBRAINZ_API_GET_ARTIST_DETAILS_FROM_ID = "http://musicbrainz.org/ws/2/artist/%s?inc=url-rels&fmt=json";
        const LASTFM_API_GET_ARTIST_DETAILS_FROM_MBID = "http://ws.audioscrobbler.com/2.0/?method=artist.getInfo&api_key=%s&mbid=%s&format=json";

        public $mbId;
        public $name;
        public $image;
        public $lastFM;
        public $musicBrainz;

	    public function __construct (string $mbId = "", string $name = "", string $image = "", string $lastFM = "", string $musicBrainz ="") {
            $this->mbId = $mbId;
            $this->name = $name;
            $this->image = $image;
            $this->lastFM = $lastFM;
            $this->musicBrainz = $musicBrainz;
        }

        public function __destruct() { }

        public static function getMusicBrainzIdFromName(string $name = "") {
            $mbId = null;
            try {
                $url = sprintf(self::MUSICBRAINZ_API_SEARCH_ARTIST_FROM_NAME, urlencode($name));
                $json = \Spieldose\Net::httpRequest($url);
                if (! empty($json)) {
                    try {
                        $mbResults = json_decode($json);
                        if (isset($mbResults->artists) && is_array($mbResults->artists) && count($mbResults->artists) > 0 && isset($mbResults->artists[0]->id)) {
                            $mbId = $mbResults->artists[0]->id;
                        }
                    } catch (\Throwable $e) {
                        // TODO: log error
                    }
                }
            } catch (\Throwable $e) {
                // TODO: log error
            }
            return($mbId);
        }

        public static function getMusicBrainzDetails(string $mbId) {
            $json = "";
            try {
                $url = sprintf(self::MUSICBRAINZ_API_GET_ARTIST_DETAILS_FROM_ID, $mbId);
                $json = \Spieldose\Net::httpRequest($url);
            } catch (\Throwable $e) {
                // TODO: log error
            }
            return($json);
        }

        public static function getLastFMDetailsFromMBId(string $mbId) {
            $json = "";
            try {
                $url = sprintf(self::LASTFM_API_GET_ARTIST_DETAILS_FROM_MBID, \Spieldose\LastFM::API_KEY, $mbId);
                $json = \Spieldose\Net::httpRequest($url);
            } catch (\Throwable $e) {
                // TODO: log error
            }
            return($json);
        }

        public static function refreshCache(\Spieldose\Database\DB $dbh, string $mbId) {
            $jsonMusicBrainz = self::getMusicBrainzDetails($mbId);
            $jsonLastFM = self::getLastFMDetailsFromMBId($mbId);
            $name = null;
            $image = "";
            if (! empty($jsonMusicBrainz)) {
                $result = json_decode($jsonMusicBrainz, false);
                if (isset($result->name) && ! empty($result->name)) {
                    $name = $result->name;
                }
                if (isset($result->relations) && count($result->relations) > 0) {
                    foreach($result->relations as $relation) {
                        if ($relation->type == "image") {
                            if (isset($relation->url) && isset($relation->url->resource) && ! empty($relation->url->resource)) {
                                $url = $relation->url->resource;
                                if (strpos($url, "https://commons.wikimedia.org/wiki/File:") === 0) {
                                    // TODO: refresh from wikimedia
                                    $imageName = str_replace("https://commons.wikimedia.org/wiki/File:", "", $url);
                                    if (! empty($imageName)) {
                                        $imageHash = md5($imageName);
                                    }
                                    $image = sprintf("https://upload.wikimedia.org/wikipedia/commons/%s/%s/%s", substr($imageHash, 0, 1), substr($imageHash, 0, 2), $imageName);
                                } else {
                                    $image = $url;
                                }
                            }

                        }
                    }
                }
            } else {
                throw new \Spieldose\Exception\NotFoundException($mbId);
            }
            if (empty($name) && ! empty($jsonLastFM)) {
                $result = json_decode($jsonLastFM, false);
                if (isset ($result->artist) && isset($result->artist->name) && ! empty($result->artist->name)) {
                    $name = $result->artist->name;
                }
            }
            echo $name . PHP_EOL;
            print_R($jsonLastFM);
            exit;

            if (! empty($name)) {
                self::saveCache($dbh, $mbId, $name, $image, $jsonLastFM, $jsonMusicBrainz );
            }
        }

        private static function saveCache(\Spieldose\Database\DB $dbh, string $mbId, string $name, string $image = "", string $jsonLastFM = "", string $jsonMusicBrainz = "") {
            $params[] = (new \Spieldose\Database\DBParam())->str(":mbid", $mbId);
            $params[] = (new \Spieldose\Database\DBParam())->str(":artist", $name);
            if (! empty($image)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":image", $image);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":image");
            }
            if (! empty($jsonLastFM)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":lastfm_json", $jsonLastFM);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":lastfm_json");
            }
            if (! empty($jsonMusicBrainz)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":musicbrainz_json", $jsonMusicBrainz);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":musicbrainz_json");
            }
            $dbh->execute("REPLACE INTO CACHE_ARTIST (mbid, artist, image, lastfm_json, musicbrainz_json) VALUES (:mbid, :artist, :image, :lastfm_json, :musicbrainz_json)", $params);
        }

    }
?>