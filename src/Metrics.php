<?php

declare(strict_types=1);

namespace Spieldose;

class Metrics
{

    public static function GetTopPlayedTracks(\aportela\DatabaseWrapper\DB $db, $filter, int $count = 5): array
    {
        $metrics = array();
        $params = array(
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.USER = :user_id "
        );
        if (isset($filter["fromDate"]) && !empty($filter["fromDate"]) && isset($filter["toDate"]) && !empty($filter["toDate"])) {
            $queryConditions[] = " strftime('%Y%m%d', S.played) BETWEEN :fromDate AND :toDate ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":fromDate", $filter["fromDate"]);
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":toDate", $filter["toDate"]);
        }
        if (isset($filter["artist"]) && !empty($filter["artist"])) {
            $queryConditions[] = " ( FIT.ARTIST LIKE :artist OR FIT.ALBUM_ARTIST LIKE :artist ) ";
            $params[] = (new \Spieldose\Database\DBParam())->str(":artist", $filter["artist"]);
        }
        $query = sprintf('
                /*
                SELECT S.file_id AS id, F.track_name AS title, COALESCE(MB.artist, F.track_artist) AS artist, MBA1.image AS image, F.genre, COALESCE(MBA1.album, F.album_name) AS album, COALESCE(MBA1.year, F.year) AS year, COALESCE(LF.loved, 0) AS loved, F.playtime_seconds AS playtimeSeconds, F.playtime_string AS playtimeString, COUNT(S.played) AS total
                */
                SELECT S.FILE AS id, FIT.TITLE AS title, FIT.ARTIST AS artist, FIT.ALBUM AS album, FIT.YEAR AS year, COALESCE(LF.loved, 0) AS loved, FIT.PLAYTIME_SECONDS AS playtimeSeconds, COUNT(S.PLAYED) AS total
                FROM PLAY_STATS S
                LEFT JOIN FILES F ON F.ID = S.FILE
                LEFT JOIN FILE_ID3_TAG FIT ON FIT.ID = F.ID
                /*
                LEFT JOIN MB_CACHE_ARTIST MB ON MB.mbid = F.artist_mbid
                LEFT JOIN MB_CACHE_ALBUM MBA1 ON MBA1.mbid = F.album_mbid
                */
                LEFT JOIN LOVED_FILE LF ON (LF.FILE = F.ID AND LF.USER = :user_id)
                %s
                GROUP BY S.FILE
                HAVING FIT.TITLE NOT NULL
                ORDER BY total DESC
                LIMIT %d;
            ', (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions) : ''), $count);
        $metrics = $db->query($query, $params);
        return ($metrics);
    }

    public static function GetTopAlbums(\aportela\DatabaseWrapper\DB $db, $filter, int $count = 5): array
    {
        $metrics = array();
        $params = array(
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.USER = :user_id "
        );
        if (isset($filter["fromDate"]) && !empty($filter["fromDate"]) && isset($filter["toDate"]) && !empty($filter["toDate"])) {
            $queryConditions[] = " strftime('%Y%m%d', S.played) BETWEEN :fromDate AND :toDate ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":fromDate", $filter["fromDate"]);
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":toDate", $filter["toDate"]);
        }
        $query = sprintf('
                /*
                SELECT COALESCE(MB.artist, F.track_artist) AS artist, COUNT(S.played) AS total
                */
                SELECT FIT.ALBUM AS album, COALESCE(FIT.album_artist, FIT.artist) AS artist, COUNT(S.PLAYED) AS total
                FROM PLAY_STATS S
                LEFT JOIN FILES F ON F.ID = S.FILE
                LEFT JOIN FILE_ID3_TAG FIT ON FIT.ID = F.ID
                /*
                LEFT JOIN MB_CACHE_ARTIST MB ON MB.mbid = F.artist_mbid
                */
                %s
                GROUP BY FIT.ALBUM
                HAVING FIT.ALBUM NOT NULL
                ORDER BY total DESC
                LIMIT %d;
            ', (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions) : ''), $count);
        $metrics = $db->query($query, $params);
        return ($metrics);
    }

    public static function GetTopArtists(\aportela\DatabaseWrapper\DB $db, $filter, int $count = 5): array
    {
        $metrics = array();
        $params = array(
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.USER = :user_id "
        );
        if (isset($filter["fromDate"]) && !empty($filter["fromDate"]) && isset($filter["toDate"]) && !empty($filter["toDate"])) {
            $queryConditions[] = " strftime('%Y%m%d', S.played) BETWEEN :fromDate AND :toDate ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":fromDate", $filter["fromDate"]);
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":toDate", $filter["toDate"]);
        }
        $query = sprintf('
                /*
                SELECT COALESCE(MB.artist, F.track_artist) AS artist, COUNT(S.played) AS total
                */
                SELECT FIT.ARTIST AS artist, COUNT(S.PLAYED) AS total
                FROM PLAY_STATS S
                LEFT JOIN FILES F ON F.ID = S.FILE
                LEFT JOIN FILE_ID3_TAG FIT ON FIT.ID = F.ID
                /*
                LEFT JOIN MB_CACHE_ARTIST MB ON MB.mbid = F.artist_mbid
                */
                %s
                GROUP BY FIT.ARTIST
                HAVING FIT.ARTIST NOT NULL
                ORDER BY total DESC
                LIMIT %d;
            ', (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions) : ''), $count);
        $metrics = $db->query($query, $params);
        return ($metrics);
    }

    public static function GetTopGenres(\aportela\DatabaseWrapper\DB $db, $filter, int $count = 5): array
    {
        $metrics = array();
        $params = array(
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.USER = :user_id "
        );
        if (isset($filter["fromDate"]) && !empty($filter["fromDate"]) && isset($filter["toDate"]) && !empty($filter["toDate"])) {
            $queryConditions[] = " strftime('%Y%m%d', S.played) BETWEEN :fromDate  AND :toDate ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":fromDate", $filter["fromDate"]);
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":toDate", $filter["toDate"]);
        }
        $query = sprintf('
                SELECT FIT.GENRE AS genre, COUNT(S.played) AS total
                FROM PLAY_STATS S
                LEFT JOIN FILES F ON F.ID = S.FILE
                LEFT JOIN FILE_ID3_TAG FIT ON FIT.ID = F.ID
                %s
                GROUP BY FIT.GENRE
                HAVING FIT.GENRE NOT NULL
                ORDER BY total DESC
                LIMIT %d;
            ', (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions) : ''), $count);
        $metrics = $db->query($query, $params);
        return ($metrics);
    }

    public static function GetRecentlyAddedTracks(\aportela\DatabaseWrapper\DB $db, $filter, int $count = 5): array
    {
        $metrics = array();
        $params = array(
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\User::getUserId())
        );
        $query = sprintf('
                /*
                SELECT F.ID AS id, F.track_name AS title, COALESCE(MB.artist, F.track_artist) AS artist, MBA1.image AS image, F.genre, COALESCE(MBA1.album, F.album_name) AS album, COALESCE(MBA1.year, F.year) AS year, COALESCE(LF.loved, 0) AS loved, F.playtime_seconds AS playtimeSeconds, F.playtime_string AS playtimeString
                */
                SELECT F.ID AS id, FIT.TITLE AS title, FIT.ARTIST AS artist, FIT.ALBUM AS album, FIT.YEAR AS year, COALESCE(LF.loved, 0) AS loved, FIT.PLAYTIME_SECONDS AS playtimeSeconds
                FROM FILES F
                LEFT JOIN FILE_ID3_TAG FIT ON FIT.ID = F.ID
                /*
                LEFT JOIN MB_CACHE_ARTIST MB ON MB.mbid = F.artist_mbid
                LEFT JOIN MB_CACHE_ALBUM MBA1 ON MBA1.mbid = F.album_mbid
                */
                LEFT JOIN LOVED_FILE LF ON (LF.FILE = F.ID AND LF.USER = :user_id)
                WHERE FIT.TITLE IS NOT NULL
                ORDER BY ATIME DESC
                LIMIT %d;
            ', $count);
        $metrics = $db->query($query, $params);
        return ($metrics);
    }

    public static function GetRecentlyAddedArtists(\aportela\DatabaseWrapper\DB $db, $filter, int $count = 5): array
    {
        $metrics = array();
        $query = sprintf('
                /*
                SELECT DISTINCT COALESCE(MB.artist, F.track_artist) AS artist
                */
                SELECT DISTINCT FIT.ARTIST AS artist
                FROM FILES F
                LEFT JOIN FILE_ID3_TAG FIT ON FIT.ID = F.ID
                /*
                LEFT JOIN MB_CACHE_ARTIST MB ON MB.mbid = F.artist_mbid
                */
                WHERE FIT.ARTIST IS NOT NULL
                ORDER BY ATIME DESC
                LIMIT %d;
            ', $count);
        $metrics = $db->query($query, array());
        return ($metrics);
    }

    public static function GetRecentlyAddedAlbums(\aportela\DatabaseWrapper\DB $db, $filter, int $count = 5): array
    {
        $metrics = array();
        $query = sprintf('
                /*
                SELECT DISTINCT COALESCE(MB2.album, F.album_name) AS album, COALESCE(MB2.artist, F.album_artist, MB1.artist, F.track_artist) AS artist
                */
                SELECT DISTINCT FIT.ALBUM AS album, COALESCE(FIT.ALBUM_ARTIST, FIT.ARTIST) AS artist
                FROM FILES F
                LEFT JOIN FILE_ID3_TAG FIT ON FIT.ID = F.ID
                /*
                LEFT JOIN MB_CACHE_ARTIST MB1 ON MB1.mbid = F.artist_mbid
                LEFT JOIN MB_CACHE_ALBUM MB2 ON MB2.mbid = F.album_mbid
                */
                WHERE COALESCE(FIT.ALBUM_ARTIST, FIT.ARTIST) IS NOT NULL
                ORDER BY ATIME DESC
                LIMIT %d;
            ', $count);
        $metrics = $db->query($query, array());
        return ($metrics);
    }

    public static function GetRecentlyPlayedTracks(\aportela\DatabaseWrapper\DB $db, $filter, int $count = 5): array
    {
        $metrics = array();
        $params = array(
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.USER = :user_id "
        );
        $query = sprintf('
                /*
                SELECT F.ID AS id, F.track_name AS title, COALESCE(MB.artist, F.track_artist) AS artist, MBA1.image AS image, F.genre, COALESCE(MBA1.album, F.album_name) AS album, COALESCE(MBA1.year, F.year) AS year, COALESCE(LF.loved, 0) AS loved, F.playtime_seconds AS playtimeSeconds, F.playtime_string AS playtimeString
                */
                SELECT F.ID AS id, FIT.TITLE AS title, FIT.ARTIST AS artist, FIT.ALBUM AS album, FIT.YEAR AS year, COALESCE(LF.loved, 0) AS loved, FIT.PLAYTIME_SECONDS AS playtimeSeconds
                FROM PLAY_STATS S
                LEFT JOIN FILES F ON F.ID = S.FILE
                LEFT JOIN FILE_ID3_TAG FIT ON FIT.ID = F.ID
                /*
                LEFT JOIN MB_CACHE_ARTIST MB ON MB.mbid = F.artist_mbid
                LEFT JOIN MB_CACHE_ALBUM MBA1 ON MBA1.mbid = F.album_mbid
                */
                LEFT JOIN LOVED_FILE LF ON (LF.FILE = F.ID AND LF.USER = :user_id)
                WHERE FIT.TITLE IS NOT NULL
                %s
                ORDER BY S.PLAYED DESC
                LIMIT %d;
            ', (count($queryConditions) > 0 ? 'AND ' . implode(" AND ", $queryConditions) : ''), $count);
        $metrics = $db->query($query, $params);
        return ($metrics);
    }

    public static function GetRecentlyPlayedArtists(\aportela\DatabaseWrapper\DB $db, $filter, int $count = 5): array
    {
        $metrics = array();
        $params = array(
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.USER = :user_id "
        );
        $query = sprintf('
                /*
                SELECT COALESCE(MB.artist, F.track_artist) AS artist
                */
                SELECT FIT.ARTIST
                FROM PLAY_STATS S
                LEFT JOIN FILES F ON F.ID = S.FILE
                LEFT JOIN FILE_ID3_TAG FIT ON FIT.ID = F.ID
                /*
                LEFT JOIN MB_CACHE_ARTIST MB ON MB.mbid = F.artist_mbid
                */
                WHERE FIT.ARTIST IS NOT NULL
                %s
                GROUP BY FIT.ARTIST
                ORDER BY MAX(S.played) DESC
                LIMIT %d;
            ', (count($queryConditions) > 0 ? 'AND ' . implode(" AND ", $queryConditions) : ''), $count);
        $metrics = $db->query($query, $params);
        return ($metrics);
    }

    public static function GetRecentlyPlayedAlbums(\aportela\DatabaseWrapper\DB $db, $filter, int $count = 5): array
    {
        $metrics = array();
        $params = array(
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.USER = :user_id "
        );
        $query = sprintf('
                /*
                SELECT COALESCE(MB2.album, F.album_name) AS album, COALESCE(MB2.artist, F.album_artist, MB1.artist, F.track_artist) AS artist
                */
                SELECT FIT.ALBUM AS album, COALESCE(FIT.ALBUM_ARTIST, FIT.ARTIST) AS artist
                FROM PLAY_STATS S
                LEFT JOIN FILES F ON F.ID = S.FILE
                LEFT JOIN FILE_ID3_TAG FIT ON FIT.ID = F.ID
                /*
                LEFT JOIN MB_CACHE_ARTIST MB1 ON MB1.mbid = F.artist_mbid
                LEFT JOIN MB_CACHE_ALBUM MB2 ON MB2.mbid = F.album_mbid
                */
                WHERE FIT.ALBUM IS NOT NULL
                %s
                GROUP BY FIT.ALBUM, COALESCE(FIT.ALBUM_ARTIST, FIT.ARTIST)
                ORDER BY MAX(S.played) DESC
                LIMIT %d;
            ', (count($queryConditions) > 0 ? 'AND ' . implode(" AND ", $queryConditions) : ''), $count);
        $metrics = $db->query($query, $params);
        return ($metrics);
    }

    public static function GetPlayStatsByHour(\aportela\DatabaseWrapper\DB $db, $filter): array
    {
        $metrics = array();
        $params = array(
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.USER = :user_id "
        );
        $query = sprintf('
                SELECT strftime("%s", S.played, "localtime") AS hour, COUNT(*) AS total
                FROM PLAY_STATS S
                %s
                GROUP BY hour
                ORDER BY hour
            ', "%H", (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions) : ''));
        $metrics = $db->query($query, $params);
        return ($metrics);
    }

    public static function GetPlayStatsByWeekDay(\aportela\DatabaseWrapper\DB $db, $filter): array
    {
        $metrics = array();
        $params = array(
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.USER = :user_id "
        );
        $query = sprintf('
                SELECT strftime("%s", S.played, "localtime") AS weekDay, COUNT(*) AS total
                FROM PLAY_STATS S
                %s
                GROUP BY weekDay
                ORDER BY weekDay
            ', "%w", (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions) : ''));
        $metrics = $db->query($query, $params);
        return ($metrics);
    }

    public static function GetPlayStatsByMonth(\aportela\DatabaseWrapper\DB $db, $filter): array
    {
        $metrics = array();
        $params = array(
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.USER = :user_id "
        );
        $query = sprintf('
                SELECT strftime("%s", S.played, "localtime") AS month, COUNT(*) AS total
                FROM PLAY_STATS S
                %s
                GROUP BY month
                ORDER BY month
            ', "%m", (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions) : ''));
        $metrics = $db->query($query, $params);
        return ($metrics);
    }

    public static function GetPlayStatsByYear(\aportela\DatabaseWrapper\DB $db, $filter): array
    {
        $metrics = array();
        $params = array(
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.USER = :user_id "
        );
        $query = sprintf('
                SELECT strftime("%s", S.played, "localtime") AS year, COUNT(*) AS total
                FROM PLAY_STATS S
                %s
                GROUP BY year
                ORDER BY year
            ', "%Y", (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions) : ''));
        $metrics = $db->query($query, $params);
        return ($metrics);
    }
}
