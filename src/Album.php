<?php

    declare(strict_types=1);

    namespace Spieldose;

    class Album {

	    public function __construct () { }

        public function __destruct() { }

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
        public static function search(\Spieldose\Database\DB $dbh, int $page = 1, int $resultsPage = 16, array $filter = array(), string $order = "") {
            $params = array();
            $whereCondition = "";
            if (isset($filter)) {
                $conditions = array();
                if (isset($filter["partialName"]) && ! empty($filter["partialName"])) {
                    $conditions[] = " COALESCE(MBA.album, F.album_name) LIKE :partialName ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":partialName", "%" . $filter["partialName"] . "%");
                }
                if (isset($filter["name"]) && ! empty($filter["name"])) {
                    $conditions[] = " COALESCE(MBA.album, F.album_name) LIKE :name ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":name", $filter["name"]);
                }
                if (isset($filter["partialArtist"]) && ! empty($filter["partialArtist"])) {
                    $conditions[] = " (MBA.artist LIKE :partialArtist OR F.album_artist LIKE :partialArtist OR F.track_artist LIKE :partialArtist) ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":partialArtist", "%" . $filter["partialArtist"] . "%");
                }
                if (isset($filter["artist"]) && ! empty($filter["artist"])) {
                    $conditions[] = " (MBA.artist LIKE :artist OR MBA2.artist LIKE :artist OR F.album_artist LIKE :artist OR F.track_artist LIKE :artist) ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":artist", $filter["artist"]);
                }
                if (isset($filter["year"]) && ! empty($filter["year"])) {
                    $conditions[] = " COALESCE(MBA.year, F.year) = :year ";
                    $params[] = (new \Spieldose\Database\DBParam())->int(":year", intval($filter["year"]));
                }
                $whereCondition = count($conditions) > 0 ? " AND " .  implode(" AND ", $conditions) : "";
            }
            $queryCount = '
                SELECT
                    COUNT (DISTINCT COALESCE(MBA.album, F.album_name) || COALESCE(MBA.artist, F.album_artist, F.track_artist, "") || COALESCE(MBA.year, F.year, 0)) AS total
                FROM FILE F
                LEFT JOIN MB_CACHE_ALBUM MBA ON MBA.mbid = F.album_mbid
                LEFT JOIN MB_CACHE_ARTIST MBA2 ON MBA2.mbid = F.artist_mbid
                WHERE COALESCE(MBA.album, F.album_name) IS NOT NULL
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
                switch($order) {
                    case "random":
                        $sqlOrder = " ORDER BY RANDOM() ";
                    break;
                    case "year":
                        $sqlOrder = " ORDER BY COALESCE(MBA.year, F.year) ASC ";
                    break;
                    default:
                        $sqlOrder = " ORDER BY COALESCE(MBA.album, F.album_name) COLLATE NOCASE ASC ";
                    break;
                }
                $query = sprintf('
                    SELECT DISTINCT
                        COALESCE(MBA.album, F.album_name) as name,
                        COALESCE(MBA.artist, F.album_artist, F.track_artist) AS artist,
                        COALESCE(MBA.year, F.year) AS year,
                        COALESCE(MBA.image, LOCAL_PATH_ALBUM_COVER.id) AS image
                    FROM FILE F
                    LEFT JOIN MB_CACHE_ALBUM MBA ON MBA.mbid = F.album_mbid
                    LEFT JOIN MB_CACHE_ARTIST MBA2 ON MBA2.mbid = F.artist_mbid
                    LEFT JOIN LOCAL_PATH_ALBUM_COVER ON LOCAL_PATH_ALBUM_COVER.base_path = F.base_path
                    WHERE COALESCE(MBA.album, F.album_name) IS NOT NULL
                    %s
                    %s
                    LIMIT %d OFFSET %d
                    ',
                    $whereCondition,
                    $sqlOrder,
                    $resultsPage,
                    $resultsPage * ($page -1)
                );
                $data->results = $dbh->query($query, $params);
            } else {
                $data->results = array();
            }
            return($data);
        }

        /**
         * save local album cover reference
         *
         * @param \Spieldose\Database\DB $dbh database handler
         * @param string $path directory path
         * @param string $filename album cover filename
         */
        public static function saveLocalAlbumCover(\Spieldose\Database\DB $dbh, string $path = "", string $filename = "") {
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
        }

        public static function getLocalPath(\Spieldose\Database\DB $dbh, string $id = "") {
            $results = $dbh->query(" SELECT base_path AS directory, file_name AS filename FROM LOCAL_PATH_ALBUM_COVER WHERE id = :id ", array(
                (new \Spieldose\Database\DBParam())->str(":id", $id)
            ));
            if (count($results) > 0) {
                return($results[0]->directory . DIRECTORY_SEPARATOR . $results[0]->filename);
            } else {
                throw new \Spieldose\Exception\NotFoundException("");
            }
        }
    }

?>