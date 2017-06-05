<?php

    namespace Spieldose;

    class LastFM
    {
        const LAST_FM_API_KEY = "40ede2a05c97a8a8055ee12f813a417d";

	    public function __construct () { }

        public function __destruct() { }

        /**
        *   make web request
        */
        private static function proxy($url) {
            $ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$referer = "http://" . parse_url($url, PHP_URL_HOST);
			curl_setopt ($ch, CURLOPT_REFERER, $referer);
			curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Chrome/0.2.153.1 Safari/525.19');
			$content = curl_exec($ch);
			curl_close($ch);
			return($content);
        }

        /**
        *   return lastfm album info (json format)
        */
        private static function getAlbumInfo($artist, $album) {
            $url = sprintf("http://ws.audioscrobbler.com/2.0/?method=album.getinfo&api_key=%s&artist=%s&album=%s&format=json", self::LAST_FM_API_KEY, $artist, $album);
            return(self::proxy($url));
        }

        /**
        *   return lastfm album images (all sizes, json format)
        */
        public static function getAlbumImages($artist, $album) {
            $json = self::getAlbumInfo($artist, $album);
            $data = json_decode($json, true);
            $images = array();
            if (isset($data["album"])) {
                $images = $data["album"]["image"];
            }
            return(json_encode($images));
        }
    }

?>
