<?php

declare(strict_types=1);

namespace Spieldose;

class Metrics
{
    /*
    DELETE FROM FILE_PLAYCOUNT_STATS;
    INSERT INTO FILE_PLAYCOUNT_STATS
    SELECT FILE.id, USER.id, datetime(strftime("%s", CURRENT_TIMESTAMP) - abs(random() % 10000000),'unixepoch')
    FROM FILE
    LEFT JOIN USER
    INNER JOIN FILE_ID3_TAG FIT ON FIT.id = FILE.id
    ORDER BY RANDOM()
    LIMIT 500;
    */

    public static function searchTracks(\aportela\DatabaseWrapper\DB $dbh, $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): array
    {
        $params = array(
            new \aportela\DatabaseWrapper\Param\IntegerParam(":count", $pager->resultsPage)
        );
        $filterConditions = array();
        $fieldDefinitions = [
            "id " => "FIT.id",
            "title" => "FIT.title",
            "artist" => "FIT.artist",
            "album" => "FIT.album",
            "albumArtist" => "FIT.album_artist",
            "year" => "FIT.year",
            "trackNumber" => "FIT.track_number",
            "musicBrainzAlbumId" => "FIT.mb_album_id",
            "coverPathId" => "D.id"
        ];
        $fieldCountDefinition = [
            "totalResults" => " COUNT(FIT.id)"
        ];

        $afterBrowseFunction = function ($data) {
            $data->items = array_map(
                function ($result) {
                    if (!empty($result->musicBrainzAlbumId)) {
                        $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\apiFormat::JSON);
                        $result->covertArtArchiveURL = $cover->getReleaseImageURL($result->musicBrainzAlbumId, \aportela\MusicBrainzWrapper\CoverArtArchiveImageType::FRONT, \aportela\MusicBrainzWrapper\CoverArtArchiveImageSize::NORMAL);
                    } else {
                        $result->covertArtArchiveURL = null;
                    }
                    return ($result);
                },
                $data->items
            );
        };

        $browser = new \aportela\DatabaseBrowserWrapper\Browser($dbh, $fieldDefinitions, $fieldCountDefinition, $pager, $sort, new \aportela\DatabaseBrowserWrapper\Filter(), $afterBrowseFunction);
        $query = null;
        switch ($sort->items[0]->field) {
            case "playCount":
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId());
                if (isset($filter["fromDate"]) && !empty($filter["fromDate"]) && isset($filter["toDate"]) && !empty($filter["toDate"])) {
                    $filterConditions[] = " strftime('%Y%m%d', FPS.play_timestamp) BETWEEN :fromDate AND :toDate ";
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":fromDate", $filter["fromDate"]);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":toDate", $filter["toDate"]);
                }
                $filterConditions[] = " FPS.user_id = :user_id ";
                $query = sprintf(
                    "
                        SELECT %s, TMP_FILE_PLAYCOUNT_STATS.playCount
                        FROM (
                            SELECT FPS.file_id, COUNT(*) AS playCount
                            FROM FILE_PLAYCOUNT_STATS FPS
                            %s
                            GROUP BY FPS.file_id
                            ORDER BY playCount %s
                            LIMIT :count
                        ) TMP_FILE_PLAYCOUNT_STATS
                        INNER JOIN FILE F ON F.id = TMP_FILE_PLAYCOUNT_STATS.file_id
                        INNER JOIN FILE_ID3_TAG FIT ON FIT.id = TMP_FILE_PLAYCOUNT_STATS.file_id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                    ",
                    $browser->getQueryFields(),
                    count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
                    $sort->items[0]->order->value
                );
                break;
            case "recentlyAdded":
                $query = sprintf(
                    "
                        SELECT %s, F.added_timestamp AS addedTimestamp
                        FROM FILE F
                        INNER JOIN FILE_ID3_TAG FIT ON FIT.id = F.id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        %s
                        ORDER BY F.added_timestamp
                        LIMIT :count
                    ",
                    $browser->getQueryFields(),
                    count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
                    $sort->items[0]->order->value
                );
                break;
            case "recentlyPlayed":
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId());
                $filterConditions[] = " FPS.user_id = :user_id ";
                /*
                if (isset($filter["fromDate"]) && !empty($filter["fromDate"]) && isset($filter["toDate"]) && !empty($filter["toDate"])) {
                    $filterConditions[] = " strftime('%Y%m%d', FPS.play_timestamp) BETWEEN :fromDate AND :toDate ";
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":fromDate", $filter["fromDate"]);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":toDate", $filter["toDate"]);
                }
                */
                $query = sprintf(
                    "
                        SELECT %s, FPS.play_timestamp AS lastPlayTimestamp
                        FROM FILE_PLAYCOUNT_STATS FPS
                        INNER JOIN FILE F ON F.id = FPS.file_id
                        INNER JOIN FILE_ID3_TAG FIT ON FIT.id = FPS.file_id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        %s
                        ORDER BY FPS.play_timestamp %s
                        LIMIT :count
                    ",
                    $browser->getQueryFields(),
                    count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
                    $sort->items[0]->order->value
                );
                break;
        }
        foreach ($params as $param) {
            $browser->addDBQueryParam($param);
        }
        $data = $browser->launch($query, "");
        return ($data->items);
    }

    public static function GetTopPlayedArtists(\aportela\DatabaseWrapper\DB $dbh, array $filter = [], int $count = 5): array
    {
        $metrics = array();
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId()),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":count", $count)
        );
        $queryConditions = array(
            " FPS.user_id = :user_id ",
        );
        if (isset($filter["fromDate"]) && !empty($filter["fromDate"]) && isset($filter["toDate"]) && !empty($filter["toDate"])) {
            $queryConditions[] = " strftime('%Y%m%d', FPS.play_timestamp) BETWEEN :fromDate AND :toDate ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":fromDate", $filter["fromDate"]);
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":toDate", $filter["toDate"]);
        }
        $fieldDefinitions = [
            "name" => "COALESCE(MB_CACHE_ARTIST.name, FIT.artist)",
            "image" => "MB_CACHE_ARTIST.image"
        ];
        $queryFields = [];
        foreach ($fieldDefinitions as $fieldAlias => $SQLfield) {
            $queryFields[] = sprintf("%s AS %s", $SQLfield, $fieldAlias);
        }
        $query = sprintf(
            '
                SELECT %s, COUNT(*) AS playCount
                FROM FILE_PLAYCOUNT_STATS FPS
                INNER JOIN FILE_ID3_TAG FIT ON FIT.id = FPS.file_id
                LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                %s
                GROUP BY COALESCE(MB_CACHE_ARTIST.name, FIT.artist)
                HAVING COALESCE(MB_CACHE_ARTIST.name, FIT.artist) NOT NULL
                ORDER BY playCount DESC
                LIMIT :count
            ',
            implode(", ", $queryFields),
            (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions) : ''),
        );
        $metrics = $dbh->query($query, $params);
        return ($metrics);
    }

    public static function GetTopPlayedAlbums(\aportela\DatabaseWrapper\DB $dbh, array $filter = [], int $count = 5): array
    {
        $metrics = array();
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId()),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":count", $count)
        );
        $queryConditions = array(
            " FPS.user_id = :user_id ",
        );
        if (isset($filter["fromDate"]) && !empty($filter["fromDate"]) && isset($filter["toDate"]) && !empty($filter["toDate"])) {
            $queryConditions[] = " strftime('%Y%m%d', FPS.play_timestamp) BETWEEN :fromDate AND :toDate ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":fromDate", $filter["fromDate"]);
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":toDate", $filter["toDate"]);
        }
        $fieldDefinitions = [
            "mbId" => "FIT.mb_album_id",
            "title" => "COALESCE(MB_CACHE_RELEASE.title, FIT.album)",
            "albumArtistName" => "COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist)",
            "albumArtistMbId" => "COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id)",
            "year" => "COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))",
            "coverPathId" => "D.id"
        ];
        $queryFields = [];
        foreach ($fieldDefinitions as $fieldAlias => $SQLfield) {
            $queryFields[] = sprintf("%s AS %s", $SQLfield, $fieldAlias);
        }
        $query = sprintf(
            '
                SELECT %s, TMP_FILE_PLAYCOUNT_STATS.playCount
                FROM (
                    SELECT FPS.file_id, COUNT(*) AS playCount
                    FROM FILE_PLAYCOUNT_STATS FPS
                    %s
                    GROUP BY FPS.file_id
                    ORDER BY playCount DESC
                    LIMIT :count
                ) TMP_FILE_PLAYCOUNT_STATS

                INNER JOIN FILE F ON F.id = TMP_FILE_PLAYCOUNT_STATS.file_id
                INNER JOIN FILE_ID3_TAG FIT ON FIT.id = TMP_FILE_PLAYCOUNT_STATS.file_id
                LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
            ',
            implode(", ", $queryFields),
            (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions) : ''),
        );
        $metrics = $dbh->query($query, $params);
        return ($metrics);
    }

    public static function GetTopPlayedGenres(\aportela\DatabaseWrapper\DB $dbh, array $filter = [], int $count = 5): array
    {
        $metrics = array();
        $params = array(
            new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId()),
            new \aportela\DatabaseWrapper\Param\IntegerParam(":count", $count)
        );
        $queryConditions = array(
            " FPS.user_id = :user_id ",
        );
        if (isset($filter["fromDate"]) && !empty($filter["fromDate"]) && isset($filter["toDate"]) && !empty($filter["toDate"])) {
            $queryConditions[] = " strftime('%Y%m%d', FPS.play_timestamp) BETWEEN :fromDate AND :toDate ";
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":fromDate", $filter["fromDate"]);
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":toDate", $filter["toDate"]);
        }
        $fieldDefinitions = [
            "name" => "FIT.genre",
        ];
        $queryFields = [];
        foreach ($fieldDefinitions as $fieldAlias => $SQLfield) {
            $queryFields[] = sprintf("%s AS %s", $SQLfield, $fieldAlias);
        }
        $query = sprintf(
            '
                SELECT %s, COUNT(*) AS playCount
                FROM FILE_PLAYCOUNT_STATS FPS
                INNER JOIN FILE_ID3_TAG FIT ON FIT.id = FPS.file_id
                %s
                GROUP BY FIT.genre
                HAVING FIT.genre NOT NULL
                ORDER BY playCount DESC
                LIMIT :count
            ',
            implode(", ", $queryFields),
            (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions) : ''),
        );
        $metrics = $dbh->query($query, $params);
        return ($metrics);
    }

    public static function GetRecentlyAddedArtists(\Spieldose\Database\DB $dbh, $filter, int $count = 5): array
    {
        $metrics = array();
        $query = sprintf('
                SELECT DISTINCT COALESCE(MB.artist, F.track_artist) AS artist
                FROM FILE F
                LEFT JOIN MB_CACHE_ARTIST MB ON MB.mbid = F.artist_mbid
                WHERE COALESCE(MB.artist, F.track_artist) IS NOT NULL
                ORDER BY created DESC
                LIMIT %d;
            ', $count);
        $metrics = $dbh->query($query, array());
        return ($metrics);
    }

    public static function GetRecentlyAddedAlbums(\Spieldose\Database\DB $dbh, $filter, int $count = 5): array
    {
        $metrics = array();
        $query = sprintf('
                SELECT DISTINCT COALESCE(MB2.album, F.album_name) AS album, COALESCE(MB2.artist, F.album_artist, MB1.artist, F.track_artist) AS artist
                FROM FILE F
                LEFT JOIN MB_CACHE_ARTIST MB1 ON MB1.mbid = F.artist_mbid
                LEFT JOIN MB_CACHE_ALBUM MB2 ON MB2.mbid = F.album_mbid
                WHERE COALESCE(MB2.album, F.album_name) IS NOT NULL
                ORDER BY created DESC
                LIMIT %d;
            ', $count);
        $metrics = $dbh->query($query, array());
        return ($metrics);
    }

    public static function GetRecentlyPlayedArtists(\Spieldose\Database\DB $dbh, $filter, int $count = 5): array
    {
        $metrics = array();
        $params = array(
            (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.user_id = :user_id "
        );
        $query = sprintf('
                SELECT COALESCE(MB.artist, F.track_artist) AS artist
                FROM STATS S
                LEFT JOIN FILE F ON F.id = S.file_id
                LEFT JOIN MB_CACHE_ARTIST MB ON MB.mbid = F.artist_mbid
                WHERE COALESCE(MB.artist, F.track_artist) IS NOT NULL
                %s
                GROUP BY COALESCE(MB.artist, F.track_artist)
                ORDER BY MAX(S.played) DESC
                LIMIT %d;
            ', (count($queryConditions) > 0 ? 'AND ' . implode(" AND ", $queryConditions) : ''), $count);
        $metrics = $dbh->query($query, $params);
        return ($metrics);
    }

    public static function GetRecentlyPlayedAlbums(\Spieldose\Database\DB $dbh, $filter, int $count = 5): array
    {
        $metrics = array();
        $params = array(
            (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.user_id = :user_id "
        );
        $query = sprintf('
                SELECT COALESCE(MB2.album, F.album_name) AS album, COALESCE(MB2.artist, F.album_artist, MB1.artist, F.track_artist) AS artist
                FROM STATS S
                LEFT JOIN FILE F ON F.id = S.file_id
                LEFT JOIN MB_CACHE_ARTIST MB1 ON MB1.mbid = F.artist_mbid
                LEFT JOIN MB_CACHE_ALBUM MB2 ON MB2.mbid = F.album_mbid
                WHERE COALESCE(MB2.album, F.album_name) IS NOT NULL
                %s
                GROUP BY COALESCE(MB2.album, F.album_name), COALESCE(MB2.artist, F.album_artist, MB1.artist, F.track_artist)
                ORDER BY MAX(S.played) DESC
                LIMIT %d;
            ', (count($queryConditions) > 0 ? 'AND ' . implode(" AND ", $queryConditions) : ''), $count);
        $metrics = $dbh->query($query, $params);
        return ($metrics);
    }

    public static function GetPlayStatsByHour(\Spieldose\Database\DB $dbh, $filter): array
    {
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
            ', "%H", (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions) : ''));
        $metrics = $dbh->query($query, $params);
        return ($metrics);
    }

    public static function GetPlayStatsByWeekDay(\Spieldose\Database\DB $dbh, $filter): array
    {
        $metrics = array();
        $params = array(
            (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.user_id = :user_id "
        );
        $query = sprintf('
                SELECT strftime("%s", S.played, "localtime") AS weekDay, COUNT(*) AS total
                FROM STATS S
                %s
                GROUP BY weekDay
                ORDER BY weekDay
            ', "%w", (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions) : ''));
        $metrics = $dbh->query($query, $params);
        return ($metrics);
    }

    public static function GetPlayStatsByMonth(\Spieldose\Database\DB $dbh, $filter): array
    {
        $metrics = array();
        $params = array(
            (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.user_id = :user_id "
        );
        $query = sprintf('
                SELECT strftime("%s", S.played, "localtime") AS month, COUNT(*) AS total
                FROM STATS S
                %s
                GROUP BY month
                ORDER BY month
            ', "%m", (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions) : ''));
        $metrics = $dbh->query($query, $params);
        return ($metrics);
    }

    public static function GetPlayStatsByYear(\Spieldose\Database\DB $dbh, $filter): array
    {
        $metrics = array();
        $params = array(
            (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId())
        );
        $queryConditions = array(
            " S.user_id = :user_id "
        );
        $query = sprintf('
                SELECT strftime("%s", S.played, "localtime") AS year, COUNT(*) AS total
                FROM STATS S
                %s
                GROUP BY year
                ORDER BY year
            ', "%Y", (count($queryConditions) > 0 ? 'WHERE ' . implode(" AND ", $queryConditions) : ''));
        $metrics = $dbh->query($query, $params);
        return ($metrics);
    }
}
