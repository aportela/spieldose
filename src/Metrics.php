<?php

    declare(strict_types=1);

    namespace Spieldose;

    class Metrics {

        public static function GetTopPlayedTracks(\Spieldose\Database\DB $dbh, $filter): array {
            $metrics = array();
            $params = array();
            $queryConditions = "";
            if (isset($filter["fromDate"]) && ! empty($filter["fromDate"]) && isset($filter["toDate"]) && ! empty($filter["toDate"])) {
                $queryConditions = " WHERE strftime('%Y%m%d', S.played) BETWEEN :fromDate  AND :toDate ";
                $params[] = (new \Spieldose\Database\DBParam())->str(":fromDate", $filter["fromDate"]);
                $params[] = (new \Spieldose\Database\DBParam())->str(":toDate", $filter["toDate"]);
            }
            $query = '
                SELECT S.file_id AS id, F.track_name AS title, F.track_artist AS artist, COUNT(S.played) AS total
                FROM STATS S
                LEFT JOIN FILE F ON F.id = S.file_id
            ' . $queryConditions . '
                GROUP BY S.file_id
                HAVING F.track_name NOT NULL
                ORDER BY total DESC
                LIMIT 5;
            ';
            $metrics = $dbh->query($query, $params);
            return($metrics);
        }

        public static function GetTopArtists(\Spieldose\Database\DB $dbh, $filter): array {
            $metrics = array();
            $params = array();
            $queryConditions = "";
            if (isset($filter["fromDate"]) && ! empty($filter["fromDate"]) && isset($filter["toDate"]) && ! empty($filter["toDate"])) {
                $queryConditions = " WHERE strftime('%Y%m%d', S.played) BETWEEN :fromDate  AND :toDate ";
                $params[] = (new \Spieldose\Database\DBParam())->str(":fromDate", $filter["fromDate"]);
                $params[] = (new \Spieldose\Database\DBParam())->str(":toDate", $filter["toDate"]);
            }
            $query = '
                SELECT F.track_artist AS name, COUNT(S.played) AS total
                FROM STATS S
                LEFT JOIN FILE F ON F.id = S.file_id
                ' . $queryConditions . '
                GROUP BY F.track_artist
                HAVING F.track_artist NOT NULL
                ORDER BY total DESC
                LIMIT 5;
            ';
            $metrics = $dbh->query($query, $params);
            return($metrics);
        }

        public static function GetTopGenres(\Spieldose\Database\DB $dbh, $filter): array {
            $metrics = array();
            $params = array();
            $queryConditions = "";
            if (isset($filter["fromDate"]) && ! empty($filter["fromDate"]) && isset($filter["toDate"]) && ! empty($filter["toDate"])) {
                $queryConditions = " WHERE strftime('%Y%m%d', S.played) BETWEEN :fromDate  AND :toDate ";
                $params[] = (new \Spieldose\Database\DBParam())->str(":fromDate", $filter["fromDate"]);
                $params[] = (new \Spieldose\Database\DBParam())->str(":toDate", $filter["toDate"]);
            }
            $query = '
                SELECT F.genre AS name, COUNT(S.played) AS total
                FROM STATS S
                LEFT JOIN FILE F ON F.id = S.file_id
                ' . $queryConditions . '
                GROUP BY F.genre
                HAVING F.genre NOT NULL
                ORDER BY total DESC
                LIMIT 5;
            ';
            $metrics = $dbh->query($query, $params);
            return($metrics);
        }

        public static function GetRecentlyAddedTracks(\Spieldose\Database\DB $dbh, $filter): array {
            $metrics = array();
            $query = '
                SELECT F.id AS id, F.track_name AS title, F.track_artist AS artist
                FROM FILE F
                WHERE F.track_name IS NOT NULL
                ORDER BY created DESC
                LIMIT 5;
            ';
            $metrics = $dbh->query($query, array());
            return($metrics);
        }

        public static function GetRecentlyAddedArtists(\Spieldose\Database\DB $dbh, $filter): array {
            $metrics = array();
            $query = '
                SELECT DISTINCT F.track_artist AS name
                FROM FILE F
                WHERE F.track_artist IS NOT NULL
                ORDER BY created DESC
                LIMIT 5;
            ';
            $metrics = $dbh->query($query, array());
            return($metrics);
        }

        public static function GetRecentlyAddedAlbums(\Spieldose\Database\DB $dbh, $filter): array {
            $metrics = array();
            $query = '
                SELECT DISTINCT F.album_name AS name, F.track_artist AS artist
                FROM FILE F
                WHERE F.album_name IS NOT NULL
                ORDER BY created DESC
                LIMIT 5;
            ';
            $metrics = $dbh->query($query, array());
            return($metrics);
        }

        public static function GetRecentlyPlayedTracks(\Spieldose\Database\DB $dbh, $filter): array {
            $metrics = array();
            $query = '
                SELECT DISTINCT F.id AS id, F.track_name AS title, F.track_artist AS artist
                FROM STATS S
                LEFT JOIN FILE F ON F.id = S.file_id
                WHERE F.track_name IS NOT NULL
                ORDER BY S.played DESC
                LIMIT 5;
            ';
            $metrics = $dbh->query($query, array());
            return($metrics);
        }

        public static function GetRecentlyPlayedArtists(\Spieldose\Database\DB $dbh, $filter): array {
            $metrics = array();
            $query = '
                SELECT DISTINCT F.track_artist AS name
                FROM STATS S
                LEFT JOIN FILE F ON F.id = S.file_id
                WHERE F.track_artist IS NOT NULL
                ORDER BY S.played DESC
                LIMIT 5;
            ';
            $metrics = $dbh->query($query, array());
            return($metrics);
        }

        public static function GetRecentlyPlayedAlbums(\Spieldose\Database\DB $dbh, $filter): array {
            $metrics = array();
            $query = '
                SELECT DISTINCT F.album_name AS name, F.track_artist AS artist
                FROM STATS S
                LEFT JOIN FILE F ON F.id = S.file_id
                WHERE F.album_name IS NOT NULL
                ORDER BY S.played DESC
                LIMIT 5;
            ';
            $metrics = $dbh->query($query, array());
            return($metrics);
        }

        public static function GetPlayStats(\Spieldose\Database\DB $dbh, $filter): array {
            $metrics = array();
            $query = '
                SELECT strftime("%H", S.played) AS hour, COUNT(*) AS total
                FROM STATS S
                GROUP BY hour
                ORDER BY hour
            ';
            $metrics = $dbh->query($query, array());
            return($metrics);
        }

    }

?>
