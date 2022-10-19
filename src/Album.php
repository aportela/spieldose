<?php

declare(strict_types=1);

namespace Spieldose;

class Album
{

    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    /**
     * search albums
     *
     * @param \Spieldose\Database\DB $dbh database handler
     * @param int $page return results from this page
     * @param int $resultsPage number of results / page
     * @param array $filter the condition filter
     * @param string $order results order
     *
     */
    public static function search(\Spieldose\Database\DB $dbh, int $page = 1, int $resultsPage = 16, array $filter = array(), string $order = "")
    {
        $params = array();
        $whereCondition = "";
        $filteredByArtist = false;
        if (isset($filter)) {
            $conditions = array();
            if (isset($filter["partialName"]) && !empty($filter["partialName"])) {
                $conditions[] = " COALESCE(MBA.ALBUM, FIT.ALBUM) LIKE :partialName ";
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":partialName", "%" . $filter["partialName"] . "%");
            }
            if (isset($filter["name"]) && !empty($filter["name"])) {
                $conditions[] = " COALESCE(MBA.album, FIT.ALBUM) LIKE :name ";
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":partialName", $filter["name"]);
            }
            if (isset($filter["partialArtist"]) && !empty($filter["partialArtist"])) {
                $conditions[] = " (MBA.ARTIST LIKE :partialArtist OR FIT.ALBUM_ARTIST LIKE :partialArtist OR FIT.ARTIST LIKE :partialArtist) ";
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":partialArtist", "%" . $filter["partialArtist"] . "%");
                $filteredByArtist = true;
            }
            if (isset($filter["artist"]) && !empty($filter["artist"])) {
                $conditions[] = " (MBA.ARTIST LIKE :artist OR MBA2.ARTIST LIKE :artist OR FIT.ALBUM_ARTIST LIKE :artist OR FIT.ARTIST LIKE :artist) ";
                $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":artist", $filter["artist"]);
                $filteredByArtist = true;
            }
            if (isset($filter["year"]) && !empty($filter["year"])) {
                $conditions[] = " COALESCE(MBA.year, F.year) = :year ";
                $params[] = new \aportela\DatabaseWrapper\Param\IntegerParam(":year", $filter["year"]);
            }
            $whereCondition = count($conditions) > 0 ? " AND " .  implode(" AND ", $conditions) : "";
        }
        $queryCount = '
                SELECT
                    COUNT (DISTINCT COALESCE(MBA.ALBUM, FIT.ALBUM) || COALESCE(MBA.ARTIST, FIT.ALBUM_ARTIST, FIT.ARTIST, "") || COALESCE(MBA.YEAR, FIT.YEAR, 0)) AS total
                FROM FILE F
                LEFT JOIN FILE_ID3_TAG FIT ON FIT.ID = F.ID
                LEFT JOIN MB_CACHE_ALBUM MBA ON MBA.MBID = FIT.MB_ALBUM_ID
                LEFT JOIN MB_CACHE_ARTIST MBA2 ON MBA2.MBID = FIT.MB_ARTIST_ID
                WHERE COALESCE(MBA.ALBUM, FIT.ALBUM) IS NOT NULL
                ' . $whereCondition . '
            ';
        $result = $dbh->query($queryCount, $params);
        $data = new \stdClass();
        $data->actualPage = $page;
        $data->resultsPage = $resultsPage;
        $data->totalResults = $result[0]->total;
        $data->totalPages = ceil($data->totalResults / $resultsPage);
        if ($data->totalResults > 0) {
            $sqlOrder = "";
            switch ($order) {
                case "random":
                    $sqlOrder = " ORDER BY RANDOM() ";
                    break;
                case "year":
                    $sqlOrder = " ORDER BY COALESCE(MBA.YEAR, FIT.YEAR, 0) ASC, COALESCE(MBA.ALBUM, FIT.ALBUM) COLLATE NOCASE ASC ";
                    break;
                default:
                    if ($filteredByArtist) {
                        $sqlOrder = ' ORDER BY COALESCE(MBA.ARTIST, FIT.ALBUM_ARTIST, FIT.ARTIST, "") COLLATE NOCASE ASC, COALESCE(MBA.YEAR, FIT.YEAR, 0) ASC, COALESCE(MBA.ALBUM, FIT.ALBUM) COLLATE NOCASE ASC ';
                    } else {
                        $sqlOrder = " ORDER BY COALESCE(MBA.ALBUM, FIT.ALBUM) COLLATE NOCASE ASC ";
                    }
                    break;
            }
            $query = sprintf(
                '
                    SELECT DISTINCT
                        COALESCE(MBA.ALBUM, FIT.ALBUM) as name,
                        COALESCE(MBA.ARTIST, FIT.ALBUM_ARTIST, FIT.ARTIST, "") AS artist,
                        COALESCE(MBA.YEAR, FIT.YEAR, 0) AS year,
                    FROM FILE F
                    LEFT JOIN FILE_ID3_TAG FIT ON FIT.ID = F.ID
                    LEFT JOIN MB_CACHE_ALBUM MBA ON MBA.MBID = FIT.MB_ALBUM_ID
                    LEFT JOIN MB_CACHE_ARTIST MBA2 ON MBA2.MBID = FIT.MB_ARTIST_ID
                    WHERE COALESCE(MBA.ALBUM, FIT.ALBUM) IS NOT NULL
                    %s
                    %s
                    LIMIT %d OFFSET %d
                    ',
                $whereCondition,
                $sqlOrder,
                $resultsPage,
                $resultsPage * ($page - 1)
            );
            $data->results = $dbh->query($query, $params);
        } else {
            $data->results = array();
        }
        return ($data);
    }

    /**
     * save local album cover reference
     *
     * @param \Spieldose\Database\DB $dbh database handler
     * @param string $path directory path
     * @param string $filename album cover filename
     */
    public static function saveLocalAlbumCover(\Spieldose\Database\DB $dbh, string $path = "", string $filename = "")
    {
        /*
            if (! empty($path) && file_exists($path)) {
                if (! empty($filename) && file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                    $params = array(
                        (new \Spieldose\Database\DBParam())->str(":id", sha1($path)),
                        (new \Spieldose\Database\DBParam())->str(":base_path", $path),
                        (new \Spieldose\Database\DBParam())->str(":file_name", $filename),
                    );
                    return($dbh->execute("REPLACE INTO LOCAL_PATH_ALBUM_COVER (id, base_path, file_name) VALUES (:id, :base_path, :file_name);", $params));
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("filename");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("path");
            }
            */
    }

    public static function getLocalPath(\Spieldose\Database\DB $dbh, string $id = "")
    {
        /*
            $results = $dbh->query(" SELECT base_path AS directory, file_name AS filename FROM LOCAL_PATH_ALBUM_COVER WHERE id = :id ", array(
                (new \Spieldose\Database\DBParam())->str(":id", $id)
            ));
            if (count($results) > 0) {
                return($results[0]->directory . DIRECTORY_SEPARATOR . $results[0]->filename);
            } else {
                throw new \Spieldose\Exception\NotFoundException("");
            }
            */
    }

    /**
     * get album cover collection
     */
    public static function getRandomAlbumCovers(\Spieldose\Database\DB $dbh, int $count = 32)
    {
        $results = $dbh->query(
            "
                    SELECT TMP.* FROM (
                        SELECT LOCAL_PATH_ALBUM_COVER.id AS hash, NULL AS url FROM LOCAL_PATH_ALBUM_COVER
                        UNION
                        SELECT NULL as hash, MB_CACHE_ALBUM.image AS url FROM MB_CACHE_ALBUM
                    ) TMP
                    ORDER BY RANDOM()
                    LIMIT :count
                ",
            array(
                (new \Spieldose\Database\DBParam())->int(":count", $count)
            )
        );
        return ($results);
    }
}
