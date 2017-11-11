<?php

    declare(strict_types=1);

    namespace Spieldose;

    class Metrics {

        public static function GetTopPlayedTracks(\Spieldose\Database\DB $dbh, $filter, int $count = 5): array {
            $metrics = array();
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
            );
            $queryConditions = array(
                " S.user_id = :user_id "
            );
            if (isset($filter["fromDate"]) && ! empty($filter["fromDate"]) && isset($filter["toDate"]) && ! empty($filter["toDate"])) {
                $queryConditions[] = " strftime('%Y%m%d', S.played) BETWEEN :fromDate  AND :toDate ";
                $params[] = (new \Spieldose\Database\DBParam())->str(":fromDate", $filter["fromDate"]);
                $params[] = (new \Spieldose\Database\DBParam())->str(":toDate", $filter["toDate"]);
            }
            if (isset($filter["artist"]) && ! empty($filter["artist"])) {
                $queryConditions[] = " COALESCE(MB.artist, F.track_artist) = :artist ";
                $params[] = (new \Spieldose\Database\DBParam())->str(":artist", $filter["artist"]);
            }
            $query = sprintf('
                SELECT S.file_id AS id, F.track_name AS title, COALESCE(MB.artist, F.track_artist) AS artist, MBA1.image AS image, F.genre, COALESCE(MBA1.album, F.album_name) AS album, COALESCE(MBA1.year, F.year) AS year, COALESCE(LF.loved, 0) AS loved, F.playtime_seconds AS playtimeSeconds, F.playtime_string AS playtimeString, COUNT(S.played) AS total
                FROM STATS S
                LEFT JOIN FILE F ON F.id = S.file_id
                LEFT JOIN MB_CACHE_ARTIST MB ON MB.mbid = F.artist_mbid
                LEFT JOIN MB_CACHE_ALBUM MBA1 ON MBA1.mbid = F.album_mbid
                LEFT JOIN LOVED_FILE LF ON (LF.file_id = F.id AND LF.user_id = :user_id)
                %s
                GROUP BY S.file_id
                HAVING title NOT NULL
                ORDER BY total DESC
                LIMIT %d;
            ', (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions): ''), $count);
            $metrics = $dbh->query($query, $params);
            return($metrics);
        }

        public static function GetTopArtists(\Spieldose\Database\DB $dbh, $filter, int $count = 5): array {
            $metrics = array();
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
            );
            $queryConditions = array(
                " S.user_id = :user_id "
            );
            if (isset($filter["fromDate"]) && ! empty($filter["fromDate"]) && isset($filter["toDate"]) && ! empty($filter["toDate"])) {
                $queryConditions[] = " strftime('%Y%m%d', S.played) BETWEEN :fromDate  AND :toDate ";
                $params[] = (new \Spieldose\Database\DBParam())->str(":fromDate", $filter["fromDate"]);
                $params[] = (new \Spieldose\Database\DBParam())->str(":toDate", $filter["toDate"]);
            }
            $query = sprintf('
                SELECT COALESCE(MB.artist, F.track_artist) AS artist, COUNT(S.played) AS total
                FROM STATS S
                LEFT JOIN FILE F ON F.id = S.file_id
                LEFT JOIN MB_CACHE_ARTIST MB ON MB.mbid = F.artist_mbid
                %s
                GROUP BY F.track_artist
                HAVING artist NOT NULL
                ORDER BY total DESC
                LIMIT %d;
            ', (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions): ''), $count);
            $metrics = $dbh->query($query, $params);
            return($metrics);
        }

        public static function GetTopGenres(\Spieldose\Database\DB $dbh, $filter, int $count = 5): array {
            $metrics = array();
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
            );
            $queryConditions = array(
                " S.user_id = :user_id "
            );
            if (isset($filter["fromDate"]) && ! empty($filter["fromDate"]) && isset($filter["toDate"]) && ! empty($filter["toDate"])) {
                $queryConditions[] = " strftime('%Y%m%d', S.played) BETWEEN :fromDate  AND :toDate ";
                $params[] = (new \Spieldose\Database\DBParam())->str(":fromDate", $filter["fromDate"]);
                $params[] = (new \Spieldose\Database\DBParam())->str(":toDate", $filter["toDate"]);
            }
            $query = sprintf('
                SELECT F.genre AS genre, COUNT(S.played) AS total
                FROM STATS S
                LEFT JOIN FILE F ON F.id = S.file_id
                %s
                GROUP BY F.genre
                HAVING genre NOT NULL
                ORDER BY total DESC
                LIMIT %d;
            ', (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions): ''), $count);
            $metrics = $dbh->query($query, $params);
            return($metrics);
        }

        public static function GetRecentlyAddedTracks(\Spieldose\Database\DB $dbh, $filter, int $count = 5): array {
            $metrics = array();
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
            );
            $query = sprintf('
                SELECT F.id AS id, F.track_name AS title, COALESCE(MB.artist, F.track_artist) AS artist, MBA1.image AS image, F.genre, COALESCE(MBA1.album, F.album_name) AS album, COALESCE(MBA1.year, F.year) AS year, COALESCE(LF.loved, 0) AS loved, F.playtime_seconds AS playtimeSeconds, F.playtime_string AS playtimeString
                FROM FILE F
                LEFT JOIN MB_CACHE_ARTIST MB ON MB.mbid = F.artist_mbid
                LEFT JOIN MB_CACHE_ALBUM MBA1 ON MBA1.mbid = F.album_mbid
                LEFT JOIN LOVED_FILE LF ON (LF.file_id = F.id AND LF.user_id = :user_id)
                WHERE title IS NOT NULL
                ORDER BY created DESC
                LIMIT %d;
            ', $count);
            $metrics = $dbh->query($query, $params);
            return($metrics);
        }

        public static function GetRecentlyAddedArtists(\Spieldose\Database\DB $dbh, $filter, int $count = 5): array {
            $metrics = array();
            $query = sprintf('
                SELECT DISTINCT COALESCE(MB.artist, F.track_artist) AS artist
                FROM FILE F
                LEFT JOIN MB_CACHE_ARTIST MB ON MB.mbid = F.artist_mbid
                WHERE artist IS NOT NULL
                ORDER BY created DESC
                LIMIT %d;
            ', $count);
            $metrics = $dbh->query($query, array());
            return($metrics);
        }

        public static function GetRecentlyAddedAlbums(\Spieldose\Database\DB $dbh, $filter, int $count = 5): array {
            $metrics = array();
            $query = sprintf('
                SELECT DISTINCT COALESCE(MB2.album, F.album_name) AS album, COALESCE(MB2.artist, MB1.artist, F.track_artist) AS artist
                FROM FILE F
                LEFT JOIN MB_CACHE_ARTIST MB1 ON MB1.mbid = F.artist_mbid
                LEFT JOIN MB_CACHE_ALBUM MB2 ON MB2.mbid = F.album_mbid
                WHERE album IS NOT NULL
                ORDER BY created DESC
                LIMIT %d;
            ', $count);
            $metrics = $dbh->query($query, array());
            return($metrics);
        }

        public static function GetRecentlyPlayedTracks(\Spieldose\Database\DB $dbh, $filter, int $count = 5): array {
            $metrics = array();
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
            );
            $queryConditions = array(
                " S.user_id = :user_id "
            );
            $query = sprintf('
                SELECT DISTINCT F.id AS id, F.track_name AS title, COALESCE(MB.artist, F.track_artist) AS artist, MBA1.image AS image, F.genre, COALESCE(MBA1.album, F.album_name) AS album, COALESCE(MBA1.year, F.year) AS year, COALESCE(LF.loved, 0) AS loved, F.playtime_seconds AS playtimeSeconds, F.playtime_string AS playtimeString
                FROM STATS S
                LEFT JOIN FILE F ON F.id = S.file_id
                LEFT JOIN MB_CACHE_ARTIST MB ON MB.mbid = F.artist_mbid
                LEFT JOIN MB_CACHE_ALBUM MBA1 ON MBA1.mbid = F.album_mbid
                LEFT JOIN LOVED_FILE LF ON (LF.file_id = F.id AND LF.user_id = :user_id)
                WHERE title IS NOT NULL
                %s
                ORDER BY S.played DESC
                LIMIT %d;
            ', (count($queryConditions) > 0 ? 'AND ' . implode(" AND ", $queryConditions): ''), $count);
            $metrics = $dbh->query($query, $params);
            return($metrics);
        }

        public static function GetRecentlyPlayedArtists(\Spieldose\Database\DB $dbh, $filter, int $count = 5): array {
            $metrics = array();
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
            );
            $queryConditions = array(
                " S.user_id = :user_id "
            );
            $query = sprintf('
                SELECT DISTINCT COALESCE(MB.artist, F.track_artist) AS artist
                FROM STATS S
                LEFT JOIN FILE F ON F.id = S.file_id
                LEFT JOIN MB_CACHE_ARTIST MB ON MB.mbid = F.artist_mbid
                WHERE artist IS NOT NULL
                %s
                ORDER BY S.played DESC
                LIMIT %d;
            ', (count($queryConditions) > 0 ? 'AND ' . implode(" AND ", $queryConditions): ''), $count);
            $metrics = $dbh->query($query, $params);
            return($metrics);
        }

        public static function GetRecentlyPlayedAlbums(\Spieldose\Database\DB $dbh, $filter, int $count = 5): array {
            $metrics = array();
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
            );
            $queryConditions = array(
                " S.user_id = :user_id "
            );
            $query = sprintf('
                SELECT DISTINCT COALESCE(MB2.album, F.album_name) AS album, COALESCE(MB2.artist, MB1.artist, F.track_artist) AS artist
                FROM STATS S
                LEFT JOIN FILE F ON F.id = S.file_id
                LEFT JOIN MB_CACHE_ARTIST MB1 ON MB1.mbid = F.artist_mbid
                LEFT JOIN MB_CACHE_ALBUM MB2 ON MB2.mbid = F.album_mbid
                WHERE album IS NOT NULL
                %s
                ORDER BY S.played DESC
                LIMIT %d;
            ', (count($queryConditions) > 0 ? 'AND ' . implode(" AND ", $queryConditions): ''), $count);
            $metrics = $dbh->query($query, $params);
            return($metrics);
        }

        public static function GetPlayStats(\Spieldose\Database\DB $dbh, $filter): array {
            $metrics = array();
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
            );
            $queryConditions = array(
                " S.user_id = :user_id "
            );
            $query = sprintf('
                SELECT strftime("%s", S.played, "localtime") AS hour, COUNT(*) AS total
                FROM STATS S
                %s
                GROUP BY hour
                ORDER BY hour
            ', "%H", (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions): ''));
            $metrics = $dbh->query($query, $params);
            return($metrics);
        }

    }

?>
