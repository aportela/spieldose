<?php

    declare(strict_types=1);

    namespace Spieldose\MusicBrainz;

    class Album {
        const USER_AGENT = 'Spieldose/1 (https://github.com/aportela/spieldose)';
        const MUSICBRAINZ_API_BASE_URL = "http://musicbrainz.org/ws/2/release-group/?query=release:%s&artistname=%s&limit=%d&fmt=json";
        const API_SEARCH_URL = "http://ws.audioscrobbler.com/2.0/?method=album.search&api_key=%s&album=%s&limit=%d&format=json";
        const API_GET_URL_FROM_MBID = "http://ws.audioscrobbler.com/2.0/?method=album.getInfo&api_key=%s&mbid=%s&format=json";
        const API_GET_URL_FROM_ALBUM_AND_ARTIST = "http://ws.audioscrobbler.com/2.0/?method=album.getInfo&api_key=%s&album=%s&artist=%s&autocorrect=1&format=json";
        public $mbId;
        public $album;
        public $artist;
        public $image;
        public $json;

	    public function __construct (string $mbId = "", string $album = "", string $artist = "", string $image = "", string $json = "") {
            $this->mbId = $mbId;
            $this->album = $album;
            $this->artist = $artist;
            $this->image = $image;
            $this->json = $json;
        }

        public function __destruct() { }

        public static function search(string $album = "", int $limit = 1): array {
            $results = array();
            $url = sprintf(self::API_SEARCH_URL, \Spieldose\LastFM::API_KEY, $album, $limit);
            $result = \Spieldose\Net::httpRequest($url);
            $result = json_decode($result);
            if (! isset($result->error)) {
                if (isset($result->results->albummatches->album) && is_array($result->results->albummatches->album)) {
                    foreach ($result->results->albummatches->album as $matchedAlbum) {
                        $results[] = new \Spieldose\MusicBrainz\Album($matchedAlbum->mbid, $matchedAlbum->name, $matchedAlbum->artist, "", "");
                    }
                }
            } else {
                throw new \Exception("MusicBrainz error: " . $result->error);
            }
            return($results);
        }

        public static function searchMusicBrainzId(string $name = "", string $artist = "", int $limit = 1): array {
            $url = sprintf(self::MUSICBRAINZ_API_BASE_URL, $name, $artist, $limit);
            $result = \Spieldose\Net::httpRequest($url, self::USER_AGENT);
            $result = json_decode($result);
            $mbIds = array();
            if (isset($result->artists)) {
                foreach($result->artists as $artist) {
                    $mbIds[] = $artist->id;
                }
            }
            return($mbIds);
        }

        private static function getBestImage($imageArray) {
            $images = array_reverse($imageArray);
            foreach($images as $image) {
                if (isset($image->size) && isset($image->{"#text"})) {
                    return($image->{"#text"});
                }
            }
            return(isset($images[0]->{"#text"}) ? $images[0]->{"#text"}: "");
        }

        public static function getFromMBId(string $mbId = "") {
            if (empty($mbId)) {
                throw new \Spieldose\Exception\InvalidParamsException("mbId");
            } else {
                $url = sprintf(self::API_GET_URL_FROM_MBID, \Spieldose\LastFM::API_KEY, $mbId);
                $json = \Spieldose\Net::httpRequest($url);
                $result = json_decode($json, false);
                if (! isset($result->error)) {
                    $image = isset($result->album->image) ? self::getBestImage($result->album->image) : "";
                    return(new \Spieldose\MusicBrainz\Album($result->album->mbid, $result->album->name, $result->album->artist, $image, $json));
                } else {
                    throw new \Exception("MusicBrainz error: " . $result->error);
                }
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
                if (! isset($result->error)) {
                    $image = isset($result->album->image) ? self::getBestImage($result->album->image) : "";
                    return(new \Spieldose\MusicBrainz\Album(isset($result->album->mbid) ? $result->album->mbid: "", isset($result->album->name) ? $result->album->name: "", isset($result->album->artist) ? $result->album->artist: "", $image, $json));
                } else {
                    throw new \Exception("MusicBrainz error: " . $result->error);
                }
            }
        }

        public function save(\Spieldose\Database\DB $dbh) {
            if (empty($this->mbId)) {
                throw new \Spieldose\Exception\InvalidParamsException("mbId");
            }
            if (empty($this->album)) {
                throw new \Spieldose\Exception\InvalidParamsException("album");
            }
            $params[] = (new \Spieldose\Database\DBParam())->str(":mbid", $this->mbId);
            $params[] = (new \Spieldose\Database\DBParam())->str(":album", $this->album);
            if (! empty($this->artist)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":artist", $this->artist);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":artist");
            }
            if (! empty($this->image)) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":image", $this->image);
            } else {
                $params[] = (new \Spieldose\Database\DBParam())->null(":image");
            }
            $params[] = (new \Spieldose\Database\DBParam())->str(":json", $this->json);
            $dbh->execute("REPLACE INTO MB_CACHE_ALBUM (mbid, album, artist, image, json) VALUES (:mbid, :album, :artist, :image, :json)", $params);
        }
    }
?>