<?php

    declare(strict_types=1);

    namespace Spieldose;

    class Net {

        /**
        *   make web request
        */
        public static function httpRequest(string $url = "", string $userAgent = "") {
            $ch = curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$referer = "http://" . parse_url($url, PHP_URL_HOST);
			curl_setopt ($ch, CURLOPT_REFERER, $referer);
            curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
            if (empty($userAgent)) {
                curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.19 (KHTML, like Gecko) Chrome/0.2.153.1 Safari/525.19');
            } else {
                curl_setopt ($ch, CURLOPT_USERAGENT, $userAgent);
            }
			$content = curl_exec($ch);
			curl_close($ch);
			return($content);
        }

    }

?>