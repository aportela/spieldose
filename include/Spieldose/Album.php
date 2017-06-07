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
            $queryCount = "SELECT COUNT(DISTINCT album) AS total FROM FILE";
            $result = $dbh->query($queryCount);
            $data = new \stdClass();
            $data->actualPage = $page;
            $data->resultsPage = $resultsPage;
            $data->totalResults = $result[0]->total;
            $data->totalPages = ceil(($data->totalResults + $resultsPage - 1) / $resultsPage);
            $sqlOrder = "";
            if (! empty($order)) {
                $sqlOrder = " ORDER BY RANDOM() ";
            } else {
                $sqlOrder = " ORDER BY FILE.album ASC, FILE.albumartist ASC, FILE.artist ASC ";
            }
            $query = sprintf(" SELECT DISTINCT album as name, artist, albumartist, year, images FROM FILE WHERE album IS NOT NULL GROUP BY COALESCE(albumartist, artist) %s LIMIT %d OFFSET %d", $sqlOrder, $resultsPage, $resultsPage * $page);
            $data->results = $dbh->query($query);
            return($data);
        }
    }

?>