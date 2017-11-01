<?php
    declare(strict_types=1);

    namespace Spieldose;

    class Album {
	    public function __construct () { }

        public function __destruct() { }

        public static function search(\Spieldose\Database\DB $dbh, int $page = 1, int $resultsPage = 16, array $filter = array(), string $order = "") {
            if ($dbh == null) {
                $dbh = new \Spieldose\Database\DB();
            }
            $params = array();
            $whereCondition = "";
            if (isset($filter)) {
                if (isset($filter["text"]) && ! empty($filter["text"])) {
                    $whereCondition = " AND COALESCE(MBA.album, F.album_name) LIKE :text ";
                    $params[] = (new \Spieldose\Database\DBParam())->str(":text", "%" . $filter["text"] . "%");
                }
            }
            $queryCount = '
                SELECT
                    COUNT (DISTINCT COALESCE(MBA.album, F.album_name) || COALESCE(F.album_artist, F.track_artist)) AS total
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
            $sqlOrder = "";
            if (! empty($order) && $order == "random") {
                $sqlOrder = " ORDER BY RANDOM() ";
            } else {
                $sqlOrder = " ORDER BY COALESCE(MBA.album, F.album_name) COLLATE NOCASE ASC ";
            }
            $query = sprintf('
                SELECT DISTINCT
                    COALESCE(MBA.album, F.album_name) as name,
                    F.track_artist AS artist,
                    F.album_artist AS albumartist,
                    COALESCE(MBA.year, F.year) AS year,
                    MBA.image
                FROM FILE F
                LEFT JOIN MB_CACHE_ALBUM MBA ON MBA.mbid = F.album_mbid
                WHERE COALESCE(MBA.album, F.album_name) IS NOT NULL
                %s
                GROUP BY COALESCE(MBA.album, F.album_name), COALESCE(F.album_artist, F.track_artist)
                %s
                LIMIT %d OFFSET %d
                ',
                $whereCondition,
                $sqlOrder,
                $resultsPage,
                $resultsPage * ($page -1)
            );
            $data->results = $dbh->query($query, $params);
            return($data);
        }

        public static function getTracks(\Spieldose\Database\DB $dbh, $album, $artist) {
            $filter = array(
                "artist" => $artist,
                "album" => $album
            );
            $data = \Spieldose\Track::search($dbh, 1, 32, $filter, "");
            return($data->results);
        }
    }

?>