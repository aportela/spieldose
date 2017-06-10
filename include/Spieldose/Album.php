<?php

    namespace Spieldose;

    class Album
    {

	    public function __construct () { }

        public function __destruct() { }

        public static function search(\Spieldose\Database $dbh, int $page = 1, int $resultsPage = 16, array $filter = array(), string $order = "") {
            if ($dbh == null) {
                $dbh = new \Spieldose\Database();
            }
            $queryCount = '
                SELECT
                    COUNT (DISTINCT(COALESCE(MBC.album, F.album_name))) AS total
                FROM FILE F
                LEFT JOIN MB_CACHE_ALBUM MBC ON MBC.mbid = F.album_mbid
                WHERE COALESCE(MBC.album, F.album_name) IS NOT NULL
            ';
            $result = $dbh->query($queryCount);
            $data = new \stdClass();
            $data->actualPage = $page;
            $data->resultsPage = $resultsPage;
            $data->totalResults = $result[0]->total;
            $data->totalPages = ceil(($data->totalResults + $resultsPage - 1) / $resultsPage);
            $sqlOrder = "";
            if (! empty($order) && $order == "random") {
                $sqlOrder = " ORDER BY RANDOM() ";
            } else {
                $sqlOrder = " ORDER BY COALESCE(album, album_name) COLLATE NOCASE ASC ";
            }
            $query = sprintf('
                SELECT DISTINCT
                    COALESCE(album, album_name) as name,
                    F.track_artist AS artist,
                    F.album_artist AS albumartist,
                    F.year,
                    MBC.image
                FROM FILE F
                LEFT JOIN MB_CACHE_ALBUM MBC ON MBC.mbid = F.album_mbid
                WHERE F.album_name IS NOT NULL
                GROUP BY COALESCE(MBC.album, F.album_name)
                %s
                LIMIT %d OFFSET %d
                ',
                $sqlOrder,
                $resultsPage,
                $resultsPage * ($page - 1)
            );
            $data->results = $dbh->query($query);
            return($data);
        }
    }

?>