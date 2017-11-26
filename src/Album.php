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
         * @param string $order results order (at this time only "random" | "")
         *
         */
        public static function search(\Spieldose\Database\DB $dbh, int $page = 1, int $resultsPage = 16, array $filter = array(), string $order = "") {
            $params = array();
            $whereCondition = "";
            if (isset($filter)) {
                if (isset($filter["partialName"]) && ! empty($filter["partialName"])) {
                    $whereCondition = " AND COALESCE(MBA.album, F.album_name) LIKE :partialName ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":partialName", "%" . $filter["partialName"] . "%");
                }
                if (isset($filter["name"]) && ! empty($filter["name"])) {
                    $whereCondition = " AND COALESCE(MBA.album, F.album_name) LIKE :name ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":name", $filter["name"]);
                }
                if (isset($filter["partialArtist"]) && ! empty($filter["partialArtist"])) {
                    $whereCondition = " AND COALESCE(MBA.artist, F.album_artist, F.track_artist) LIKE :partialArtist ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":partialArtist", "%" . $filter["partialArtist"] . "%");
                }
                if (isset($filter["artist"]) && ! empty($filter["artist"])) {
                    $whereCondition = " AND COALESCE(MBA.artist, F.album_artist, F.track_artist) LIKE :artist ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":artist", $filter["artist"]);
                }
                if (isset($filter["year"]) && ! empty($filter["year"])) {
                    $whereCondition = " AND COALESCE(MBA.year, F.year) = :year ";
                    $params[] = (new \Spieldose\Database\DBParam())->int(":year", intval($filter["year"]));
                }
            }
            $queryCount = '
                SELECT
                    COUNT (DISTINCT COALESCE(MBA.album, F.album_name) || COALESCE(MBA.artist, F.album_artist, F.track_artist) || COALESCE(MBA.year, F.year)) AS total
                FROM FILE F
                LEFT JOIN MB_CACHE_ALBUM MBA ON MBA.mbid = F.album_mbid
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
                        MBA.image
                    FROM FILE F
                    LEFT JOIN MB_CACHE_ALBUM MBA ON MBA.mbid = F.album_mbid
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

    }

?>