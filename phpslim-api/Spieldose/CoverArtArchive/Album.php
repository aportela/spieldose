<?php

    declare(strict_types=1);

    namespace Spieldose\CovertArtArchive;

    class Album {

        const API_SEARCH_URL = "http://coverartarchive.org/%s/front";

        public static function get(string $mbId) {
            $url = sprintf(self::API_SEARCH_URL, $mbId);
        }
    }
?>