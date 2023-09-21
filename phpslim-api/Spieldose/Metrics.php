<?php

declare(strict_types=1);

namespace Spieldose;

class Metrics
{
    /*
    DELETE FROM FILE_PLAYCOUNT_STATS;
    INSERT INTO FILE_PLAYCOUNT_STATS
    SELECT FILE.id, USER.id, strftime("%s", CURRENT_TIMESTAMP) - abs(random() % 10000000)
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
            "mbId" => "FIT.mb_release_track_id",
            "title" => "FIT.title",
            "artistMBId" => "FIT.mb_artist_id",
            "artistName" => "COALESCE(MB_CACHE_ARTIST.name, FIT.artist)",
            "releaseMBId" => "FIT.mb_album_id",
            "releaseTitle" => "COALESCE(MB_CACHE_RELEASE.title, FIT.album)",
            "albumArtistMBId" => "COALESCE(MB_CACHE_RELEASE.artist_mbid, FIT.mb_album_artist_id)",
            "albumArtistName" => "COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist)",
            "year" => "COALESCE(MB_CACHE_RELEASE.year, CAST(FIT.year AS INT))",
            "trackNumber" => "FIT.track_number",
            "coverPathId" => "D.id"
        ];
        $fieldCountDefinition = [
            "totalResults" => " COUNT(FIT.id)"
        ];

        $afterBrowseFunction = function ($data) use ($sort) {
            $data->items = array_map(
                function ($result) use ($sort) {
                    $newResult = new \stdClass();
                    $newResult->track = new \Spieldose\Entities\Track(
                        $result->id,
                        $result->mbId,
                        $result->title,
                        $result->artistMBId,
                        $result->artistName,
                        $result->releaseMBId,
                        $result->releaseTitle,
                        $result->albumArtistMBId,
                        $result->albumArtistName,
                        $result->year,
                        $result->trackNumber,
                        $result->coverPathId
                    );
                    switch ($sort->items[0]->field) {
                        case "playCount":
                            $newResult->playCount = $result->playCount;
                            break;
                        case "recentlyAdded":
                            $newResult->addedTimestamp = $result->addedTimestamp;
                            break;
                        case "recentlyPlayed":
                            $newResult->lastPlayTimestamp = $result->lastPlayTimestamp;
                            break;
                    }
                    return ($newResult);
                },
                $data->items
            );
        };

        $browser = new \aportela\DatabaseBrowserWrapper\Browser($dbh, $fieldDefinitions, $fieldCountDefinition, $pager, $sort, new \aportela\DatabaseBrowserWrapper\Filter(), $afterBrowseFunction);
        $query = null;
        switch ($sort->items[0]->field) {
            case "playCount":
                if (!(isset($filter["global"]) && $filter["global"])) {
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId());
                    $filterConditions[] = " FPS.user_id = :user_id ";
                }
                if (isset($filter["fromDate"]) && !empty($filter["fromDate"]) && isset($filter["toDate"]) && !empty($filter["toDate"])) {
                    $filterConditions[] = " strftime('%Y%m%d', datetime(FPS.play_timestamp, 'unixepoch')) BETWEEN :fromDate AND :toDate ";
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":fromDate", $filter["fromDate"]);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":toDate", $filter["toDate"]);
                }
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
                        INNER JOIN FILE_ID3_TAG FIT ON FIT.id = TMP_FILE_PLAYCOUNT_STATS.file_id
                        INNER JOIN FILE F ON F.id = FIT.id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                        LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
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
                        FROM FILE_ID3_TAG FIT
                        INNER JOIN FILE F ON F.id = FIT.id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                        LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                        %s
                        ORDER BY F.added_timestamp %s
                        LIMIT :count
                    ",
                    $browser->getQueryFields(),
                    count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
                    $sort->items[0]->order->value
                );
                break;
            case "recentlyPlayed":
                if (!(isset($filter["global"]) && $filter["global"])) {
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId());
                    $filterConditions[] = " FPS.user_id = :user_id ";
                }
                $query = sprintf(
                    "
                        SELECT %s, FPS.play_timestamp AS lastPlayTimestamp
                        FROM FILE_PLAYCOUNT_STATS FPS
                        INNER JOIN FILE_ID3_TAG FIT ON FIT.id = FPS.file_id
                        INNER JOIN FILE F ON F.id = FIT.id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                        LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
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

    public static function searchArtists(\aportela\DatabaseWrapper\DB $dbh, $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): array
    {
        $params = array(
            new \aportela\DatabaseWrapper\Param\IntegerParam(":count", $pager->resultsPage)
        );
        $filterConditions = array();
        $fieldDefinitions = [
            "name" => "COALESCE(MB_CACHE_ARTIST.name, FIT.artist)",
            "image" => "MB_CACHE_ARTIST.image"
        ];

        $fieldCountDefinition = [
            "totalResults" => " COUNT(1)"
        ];

        $afterBrowseFunction = function ($data) {
            $data->items = array_map(
                function ($result) {
                    if (!empty($result->musicBrainzAlbumId)) {
                        $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
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
                if (!(isset($filter["global"]) && $filter["global"])) {
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId());
                    $filterConditions[] = " FPS.user_id = :user_id ";
                }
                if (isset($filter["fromDate"]) && !empty($filter["fromDate"]) && isset($filter["toDate"]) && !empty($filter["toDate"])) {
                    $filterConditions[] = " strftime('%Y%m%d', datetime(FPS.play_timestamp, 'unixepoch')) BETWEEN :fromDate AND :toDate ";
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":fromDate", $filter["fromDate"]);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":toDate", $filter["toDate"]);
                }
                $query = sprintf(
                    "
                        SELECT %s, COUNT(*) AS playCount
                        FROM FILE_PLAYCOUNT_STATS FPS
                        INNER JOIN FILE_ID3_TAG FIT ON FIT.id = FPS.file_id
                        LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                        %s
                        GROUP BY COALESCE(MB_CACHE_ARTIST.name, FIT.artist)
                        HAVING COALESCE(MB_CACHE_ARTIST.name, FIT.artist) NOT NULL
                        ORDER BY playCount %s
                        LIMIT :count
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
                        LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        %s
                        GROUP BY COALESCE(MB_CACHE_ARTIST.name, FIT.artist)
                        HAVING COALESCE(MB_CACHE_ARTIST.name, FIT.artist) NOT NULL
                        ORDER BY F.added_timestamp
                        LIMIT :count
                    ",
                    $browser->getQueryFields(),
                    count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
                    $sort->items[0]->order->value
                );
                break;
            case "recentlyPlayed":
                if (!(isset($filter["global"]) && $filter["global"])) {
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId());
                    $filterConditions[] = " FPS.user_id = :user_id ";
                }
                $query = sprintf(
                    "
                        SELECT %s, FPS.play_timestamp AS lastPlayTimestamp
                        FROM FILE_PLAYCOUNT_STATS FPS
                        INNER JOIN FILE F ON F.id = FPS.file_id
                        INNER JOIN FILE_ID3_TAG FIT ON FIT.id = FPS.file_id
                        LEFT JOIN MB_CACHE_ARTIST ON MB_CACHE_ARTIST.mbid = FIT.mb_artist_id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        %s
                        GROUP BY COALESCE(MB_CACHE_ARTIST.name, FIT.artist)
                        HAVING COALESCE(MB_CACHE_ARTIST.name, FIT.artist) NOT NULL
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

    public static function searchAlbums(\aportela\DatabaseWrapper\DB $dbh, $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): array
    {
        $params = array(
            new \aportela\DatabaseWrapper\Param\IntegerParam(":count", $pager->resultsPage)
        );
        $filterConditions = array();
        $fieldDefinitions = [
            "title" => "COALESCE(MB_CACHE_RELEASE.title, FIT.album)",
            "albumArtistName" => "COALESCE(MB_CACHE_RELEASE.artist_name, FIT.album_artist)",
            "year" => "COALESCE(MB_CACHE_RELEASE.year, FIT.year)"
        ];

        $fieldCountDefinition = [
            "totalResults" => " COUNT(1)"
        ];

        $afterBrowseFunction = function ($data) {
            $data->items = array_map(
                function ($result) {
                    if (!empty($result->musicBrainzAlbumId)) {
                        $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
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
                if (!(isset($filter["global"]) && $filter["global"])) {
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId());
                    $filterConditions[] = " FPS.user_id = :user_id ";
                }
                if (isset($filter["fromDate"]) && !empty($filter["fromDate"]) && isset($filter["toDate"]) && !empty($filter["toDate"])) {
                    $filterConditions[] = " strftime('%Y%m%d', datetime(FPS.play_timestamp, 'unixepoch')) BETWEEN :fromDate AND :toDate ";
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":fromDate", $filter["fromDate"]);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":toDate", $filter["toDate"]);
                }
                $query = sprintf(
                    "
                        SELECT %s, COUNT(*) AS playCount
                        FROM FILE_PLAYCOUNT_STATS FPS
                        INNER JOIN FILE_ID3_TAG FIT ON FIT.id = FPS.file_id
                        LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                        %s
                        GROUP BY COALESCE(MB_CACHE_RELEASE.title, FIT.album)
                        HAVING COALESCE(MB_CACHE_RELEASE.title, FIT.album) NOT NULL
                        ORDER BY playCount %s
                        LIMIT :count
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
                        LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        %s
                        GROUP BY COALESCE(MB_CACHE_RELEASE.title, FIT.album)
                        HAVING COALESCE(MB_CACHE_RELEASE.title, FIT.album) NOT NULL
                        ORDER BY F.added_timestamp
                        LIMIT :count
                    ",
                    $browser->getQueryFields(),
                    count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
                    $sort->items[0]->order->value
                );
                break;
            case "recentlyPlayed":
                if (!(isset($filter["global"]) && $filter["global"])) {
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId());
                    $filterConditions[] = " FPS.user_id = :user_id ";
                }
                $query = sprintf(
                    "
                        SELECT %s, FPS.play_timestamp AS lastPlayTimestamp
                        FROM FILE_PLAYCOUNT_STATS FPS
                        INNER JOIN FILE F ON F.id = FPS.file_id
                        INNER JOIN FILE_ID3_TAG FIT ON FIT.id = FPS.file_id
                        LEFT JOIN MB_CACHE_RELEASE ON MB_CACHE_RELEASE.mbid = FIT.mb_album_id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        %s
                        GROUP BY COALESCE(MB_CACHE_RELEASE.title, FIT.album)
                        HAVING COALESCE(MB_CACHE_RELEASE.title, FIT.album) NOT NULL
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

    public static function searchGenres(\aportela\DatabaseWrapper\DB $dbh, $filter, \aportela\DatabaseBrowserWrapper\Sort $sort, \aportela\DatabaseBrowserWrapper\Pager $pager): array
    {
        $params = array(
            new \aportela\DatabaseWrapper\Param\IntegerParam(":count", $pager->resultsPage)
        );
        $filterConditions = array();
        $fieldDefinitions = [
            "name" => "FIT.genre",
        ];

        $fieldCountDefinition = [
            "totalResults" => " COUNT(1)"
        ];

        $afterBrowseFunction = function ($data) {
            $data->items = array_map(
                function ($result) {
                    if (!empty($result->musicBrainzAlbumId)) {
                        $cover = new \aportela\MusicBrainzWrapper\CoverArtArchive(new \Psr\Log\NullLogger(""), \aportela\MusicBrainzWrapper\APIFormat::JSON);
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
                if (!(isset($filter["global"]) && $filter["global"])) {
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId());
                    $filterConditions[] = " FPS.user_id = :user_id ";
                }
                if (isset($filter["fromDate"]) && !empty($filter["fromDate"]) && isset($filter["toDate"]) && !empty($filter["toDate"])) {
                    $filterConditions[] = " strftime('%Y%m%d', datetime(FPS.play_timestamp, 'unixepoch')) BETWEEN :fromDate AND :toDate ";
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":fromDate", $filter["fromDate"]);
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":toDate", $filter["toDate"]);
                }
                $query = sprintf(
                    "
                        SELECT %s, COUNT(*) AS playCount
                        FROM FILE_PLAYCOUNT_STATS FPS
                        INNER JOIN FILE_ID3_TAG FIT ON FIT.id = FPS.file_id
                        %s
                        GROUP BY FIT.genre
                        HAVING FIT.genre NOT NULL
                        ORDER BY playCount %s
                        LIMIT :count
                    ",
                    $browser->getQueryFields(),
                    count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
                    $sort->items[0]->order->value
                );
                break;
            case "recentlyAdded":
                $query = sprintf(
                    "
                        SELECT %s, MAX(F.added_timestamp) AS addedTimestamp
                        FROM FILE F
                        INNER JOIN FILE_ID3_TAG FIT ON FIT.id = F.id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        %s
                        GROUP BY FIT.genre
                        HAVING FIT.genre NOT NULL
                        ORDER BY F.added_timestamp
                        LIMIT :count
                    ",
                    $browser->getQueryFields(),
                    count($filterConditions) > 0 ? " WHERE " . implode(" AND ", $filterConditions) : null,
                    $sort->items[0]->order->value
                );
                break;
            case "recentlyPlayed":
                if (!(isset($filter["global"]) && $filter["global"])) {
                    $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId());
                    $filterConditions[] = " FPS.user_id = :user_id ";
                }
                $query = sprintf(
                    "
                        SELECT %s, MAX(FPS.play_timestamp) AS lastPlayTimestamp
                        FROM FILE_PLAYCOUNT_STATS FPS
                        INNER JOIN FILE F ON F.id = FPS.file_id
                        INNER JOIN FILE_ID3_TAG FIT ON FIT.id = FPS.file_id
                        LEFT JOIN DIRECTORY D ON D.ID = F.directory_id AND D.cover_filename IS NOT NULL
                        %s
                        GROUP BY FIT.genre
                        HAVING FIT.genre NOT NULL
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

    public static function searchPlaysByDateRange(\aportela\DatabaseWrapper\DB $dbh, array $filter): array
    {
        $format = null;
        if (isset($filter["dateRange"])) {
            switch ($filter["dateRange"]) {
                case "hour":
                    $format = "%H";
                    break;
                case "weekday":
                    $format = "%w";
                    break;
                case "month":
                    $format = "%m";
                    break;
                case "year":
                    $format = "%Y";
                    break;
                case "fullDate":
                    $format = "%Y%m%d";
                    break;
                default:
                    throw new \Spieldose\Exception\InvalidParamsException(("dateRange"));
                    break;
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException(("dateRange"));
        }
        $params = [];
        $whereConditions = [];
        if (!(isset($filter["global"]) && $filter["global"])) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":user_id", \Spieldose\UserSession::getUserId());
            $whereConditions[] = " FPS.user_id = :user_id ";
        }
        if (isset($filter["trackId"]) && !empty($filter["trackId"])) {
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":file_id", $filter["trackId"]);
            $whereConditions[] = " FPS.file_id = :file_id ";
        }
        $query = sprintf('
            SELECT strftime("%s", datetime(FPS.play_timestamp, "unixepoch"), "localtime") AS %s, COUNT(*) AS total
            FROM FILE_PLAYCOUNT_STATS FPS
            %s
            GROUP BY 1
            ORDER BY 1
        ', $format, $filter["dateRange"], count($whereConditions) > 0 ? " WHERE " . implode(" AND ", $whereConditions) : null);
        return ($dbh->query($query, $params));
    }

    public static function searchPlaysByUser(\aportela\DatabaseWrapper\DB $dbh, array $filter): array
    {
        $params = [];
        $whereCondition = null;
        if (isset($filter["fromDate"]) && !empty($filter["fromDate"]) && isset($filter["toDate"]) && !empty($filter["toDate"])) {
            $whereCondition = " WHERE strftime('%Y%m%d', datetime(FPS.play_timestamp, 'unixepoch')) BETWEEN :fromDate AND :toDate ";
            $params = array(
                new \aportela\DatabaseWrapper\Param\StringParam(":fromDate", $filter["fromDate"]),
                new \aportela\DatabaseWrapper\Param\StringParam(":toDate", $filter["toDate"])
            );
        }
        $query = sprintf('
            SELECT U.name, COUNT(*) AS total
            FROM FILE_PLAYCOUNT_STATS FPS
            INNER JOIN USER U ON U.id = FPS.user_id
            %s
            GROUP BY FPS.user_id
            ORDER BY 1
        ', $whereCondition);
        return ($dbh->query($query, $params));
    }
}
